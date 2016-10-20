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
 * Indexible voter interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface IndexibleVoterInterface
{
    const VOTE_ALLOW = 1;
    const VOTE_DENY = -1;

    /**
     * @param DocumentDescriptor $descriptor
     *
     * @return bool
     */
    public function isIndexible(DocumentDescriptor $descriptor);
}
