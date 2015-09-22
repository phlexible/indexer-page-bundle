<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Content identifier
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
     * @param SiterootManagerInterface    $siterootManager
     * @param ContentTreeManagerInterface $treeManager
     * @param ElementService              $elementService
     */
    public function __construct(
        SiterootManagerInterface $siterootManager,
        ContentTreeManagerInterface $treeManager,
        ElementService $elementService
    ) {
        $this->siterootManager = $siterootManager;
        $this->treeManager = $treeManager;
        $this->elementService = $elementService;
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
            $skipElementTypes = explode(';', $siteroot->getProperty('element_indexer.skip_elementtype_ids'));

            if ($siterootDisabled) {
                continue;
            }

            $rii = new \RecursiveIteratorIterator(
                $tree->getIterator(),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($rii as $node) {
                /* @var $node TreeNodeInterface */

                if ($tree->isInstance($node) && !$tree->isInstanceMaster($node)) {
                    continue;
                }

                /**
                 * skip specific tids
                 */
                if ($node->getAttribute('searchNoIndex', false)) {
                    continue;
                }

                foreach ($tree->getPublishedVersions($node) as $language => $onlineVersion) {
                    $element = $this->elementService->findElement($node->getTypeId());

                    /**
                     * skip specific element types
                     */
                    if (in_array($element->getElementtypeId(), $skipElementTypes)) {
                        continue;
                    }

                    $elementtype = $this->elementService->findElementtype($element);
                    if ('full' !== $elementtype->getType()) {
                        continue;
                    }

                    $node = $this->treeManager->find($siteroot->getId())->get($node->getId());
                    $descriptors[] = new DocumentDescriptor(
                        $this->createIdentity($node, $language),
                        $node,
                        $siteroot,
                        $language
                    );
                }
            }
        }

        return $descriptors;
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     *
     * @return DocumentIdentity
     */
    private function createIdentity(TreeNodeInterface $node, $language)
    {
        return new DocumentIdentity("element_{$node->getId()}_$language");
    }

    /**
     * @param DocumentIdentity $identity
     *
     * @return array|null
     */
    private function matchIdentity(DocumentIdentity $identity)
    {
        if (!preg_match('/^element_(\d+)_(\w\w)$/', $identity->getIdentifier(), $match)) {
            return null;
        }

        return $match;
    }
}
