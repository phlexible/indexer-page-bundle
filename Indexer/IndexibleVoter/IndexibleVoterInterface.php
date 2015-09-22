<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;

/**
 * Indexible voter interface
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
