<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentDescriptor;

/**
 * Chain indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainIndexibleVoter implements IndexibleVoterInterface
{
    /**
     * @var IndexibleVoterInterface[]
     */
    private $voters;

    /**
     * @param IndexibleVoterInterface[] $voters
     */
    public function __construct(array $voters)
    {
        $this->voters = $voters;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexible(DocumentDescriptor $descriptor)
    {
        foreach ($this->voters as $voter) {
            $result = $voter->isIndexible($descriptor);

            if (self::VOTE_DENY === $result) {
                return self::VOTE_DENY;
            }
        }

        return self::VOTE_ALLOW;
    }
}
