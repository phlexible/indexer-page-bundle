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
 * Node indexible voter
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
    public function isIndexible(DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();
        $language = $descriptor->getLanguage();

        $skipNodeIds = explode(',', $siteroot->getProperty('element_indexer.skip_node_ids'));

        if (!$node->getTree()->getPublishedVersion($node, $language)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node not published");

            return self::VOTE_DENY;
        }

        // skip tid?
        if ($node->getAttribute('searchNoIndex', false)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node is marked with no-index");

            return self::VOTE_DENY;
        }

        if (in_array($node->getId(), $skipNodeIds)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, node id in skip node list");

            return self::VOTE_DENY;
        }

        return self::VOTE_ALLOW;
    }
}
