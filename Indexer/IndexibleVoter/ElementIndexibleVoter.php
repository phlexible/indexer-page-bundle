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

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\ElementtypeBundle\Model\Elementtype;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Psr\Log\LoggerInterface;

/**
 * Element indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementIndexibleVoter implements IndexibleVoterInterface
{
    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ElementService  $elementService
     * @param LoggerInterface $logger
     */
    public function __construct(ElementService $elementService, LoggerInterface $logger)
    {
        $this->elementService = $elementService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexible(PageDocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();

        $skipElementtypeIds = explode(',', $siteroot->getProperty('page_indexer.skip_elementtype_ids'));

        $element = $this->elementService->findElement($node->getTypeId());

        // skip configured element types
        if (in_array($element->getElementtypeId(), $skipElementtypeIds)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, elementtype id in skip list");

            return self::VOTE_DENY;
        }

        $elementtype = $this->elementService->findElementtype($element);

        // skip non full element types
        if (Elementtype::TYPE_FULL !== $elementtype->getType()) {
            // ElementtypeVersion::TYPE_FULL
            $this->logger->info("TreeNode {$node->getId()} not indexed, not a full element");

            return self::VOTE_DENY;
        }

        return self::VOTE_ALLOW;
    }
}
