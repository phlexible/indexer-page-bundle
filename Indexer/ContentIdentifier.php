<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Content identifier.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentIdentifier implements ContentIdentifierInterface
{
    /**
     * @var SiterootManagerInterface
     */
    private $siterootManager;

    /**
     * @var ContentTreeManagerInterface
     */
    private $treeManager;

    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleVoter;

    /**
     * @param SiterootManagerInterface    $siterootManager
     * @param ContentTreeManagerInterface $treeManager
     * @param ElementService              $elementService
     * @param IndexibleVoterInterface     $indexibleVoter
     */
    public function __construct(
        SiterootManagerInterface $siterootManager,
        ContentTreeManagerInterface $treeManager,
        ElementService $elementService,
        IndexibleVoterInterface $indexibleVoter
    ) {
        $this->siterootManager = $siterootManager;
        $this->treeManager = $treeManager;
        $this->elementService = $elementService;
        $this->indexibleVoter = $indexibleVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function validateIdentity(DocumentIdentity $identity)
    {
        return (bool) $this->matchIdentity($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function createDescriptorFromNode(TreeNodeInterface $node, $language)
    {
        $siteroot = $this->siterootManager->find($node->getTree()->getSiterootId());

        if (!$siteroot) {
            return null;
        }

        return new DocumentDescriptor($this->createIdentity($node, $language), $node, $siteroot, $language);
    }

    /**
     * {@inheritdoc}
     */
    public function createDescriptorFromIdentity(DocumentIdentity $identity)
    {
        $match = $this->matchIdentity($identity);
        if (!$match) {
            return null;
        }

        $nodeId = $match[1];
        $language = $match[2];

        $tree = $this->treeManager->findByTreeId($nodeId);
        if (!$tree) {
            return null;
        }

        $node = $tree->get($nodeId);
        if (!$node) {
            return null;
        }

        $siteroot = $this->siterootManager->find($tree->getSiterootId());
        if (!$node) {
            return null;
        }

        return new DocumentDescriptor($identity, $node, $siteroot, $language);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllDescriptors()
    {
        $descriptors = array();

        foreach ($this->treeManager->findAll() as $tree) {
            $siteroot = $this->siterootManager->find($tree->getSiterootId());

            // get siteroot properties
            $siterootDisabled = $siteroot->getProperty('element_indexer.disabled');

            if ($siterootDisabled) {
                continue;
            }

            $rii = new \RecursiveIteratorIterator(
                $tree->getIterator(),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($rii as $node) {
                /* @var $node TreeNodeInterface */

                $contentNode = $this->treeManager->find($siteroot->getId())->get($node->getId());

                foreach ($tree->getPublishedVersions($node) as $language => $onlineVersion) {
                    $descriptor = new DocumentDescriptor(
                        $this->createIdentity($contentNode, $language),
                        $contentNode,
                        $siteroot,
                        $language
                    );

                    if ($this->indexibleVoter->isIndexible($descriptor) === IndexibleVoterInterface::VOTE_DENY) {
                        continue;
                    }

                    yield $descriptor;

                    $descriptors[] = $descriptor;
                }
            }
        }
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     *
     * @return DocumentIdentity
     */
    protected function createIdentity(TreeNodeInterface $node, $language)
    {
        return new DocumentIdentity("element_{$node->getId()}_$language");
    }

    /**
     * @param DocumentIdentity $identity
     *
     * @return array|null
     */
    protected function matchIdentity(DocumentIdentity $identity)
    {
        if (!preg_match('/^element_(\d+)_(\w\w)$/', $identity->getIdentifier(), $match)) {
            return null;
        }

        return $match;
    }
}
