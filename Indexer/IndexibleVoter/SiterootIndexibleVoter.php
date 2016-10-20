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
use Psr\Log\LoggerInterface;

/**
 * Siteroot indexible voter
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class SiterootIndexibleVoter implements IndexibleVoterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexible(DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();

        $siterootDisabled  = (bool) $siteroot->getProperty('element_indexer.disabled');

        // skip siteroot?
        if ($siterootDisabled) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, siteroot is disabled");

            return self::VOTE_DENY;
        }

        return self::VOTE_ALLOW;
    }
}
