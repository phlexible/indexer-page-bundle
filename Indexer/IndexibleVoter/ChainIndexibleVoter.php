<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Psr\Log\LoggerInterface;

/**
 * Chain indexible voter
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
