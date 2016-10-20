<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Psr\Log\LoggerInterface;

/**
 * Node indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class NodeIndexibleVoter implements IndexibleVoterInterface
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
    public function /*
         * This file is part of the phlexible indexer page package.
         *
         * (c) Stephan Wentz <sw@brainbits.net>
         *
         * For the full copyright and license information, please view the LICENSE
         * file that was distributed with this source code.
         */
    isIndexible(DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();
        $language = $descriptor->getLanguage();

        $skipNodeIds = explode(',', $siteroot->getProperty('element_indexer.skip_node_ids'));

        // skip redirect nodes
        if ($node->getField('forward')) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node is redirect");

            return self::VOTE_DENY;
        }

        // skip not published nodes
        if (!$node->getTree()->getPublishedVersion($node, $language)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node not published");

            return self::VOTE_DENY;
        }

        // skip nodes with disabled indexing
        if ($node->getAttribute('searchNoIndex', false)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node is marked with no-index");

            return self::VOTE_DENY;
        }

        // skip configured skip nodes
        if (in_array($node->getId(), $skipNodeIds)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node id in skip node list");

            return self::VOTE_DENY;
        }

        // skip slave instances
        if ($node->getTree()->isInstance($node) && !$node->getTree()->isInstanceMaster($node)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, not instance master");

            return self::VOTE_DENY;
        }

        return self::VOTE_ALLOW;
    }
}
