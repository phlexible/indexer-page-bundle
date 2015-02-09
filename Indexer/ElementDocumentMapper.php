<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\ElementBundle\Entity\ElementVersion;
use Phlexible\Bundle\ElementRendererBundle\Configurator\Configuration;
use Phlexible\Bundle\ElementRendererBundle\Configurator\ConfiguratorInterface;
use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerElementBundle\IndexerElementEvents;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Element document mapper
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementDocumentMapper
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

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
     * @var ConfiguratorInterface
     */
    private $configurator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param DocumentFactory             $documentFactory
     * @param SiterootManagerInterface    $siterootManager
     * @param ContentTreeManagerInterface $treeManager
     * @param ElementService              $elementService
     * @param ConfiguratorInterface       $configurator
     * @param RouterInterface             $router
     * @param EngineInterface             $templating
     * @param EventDispatcherInterface    $dispatcher
     * @param LoggerInterface             $logger
     * @param ContainerInterface          $container
     */
    public function __construct(
        DocumentFactory $documentFactory,
        SiterootManagerInterface $siterootManager,
        ContentTreeManagerInterface $treeManager,
        ElementService $elementService,
        ConfiguratorInterface $configurator,
        RouterInterface $router,
        EngineInterface $templating,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger,
        ContainerInterface $container)
    {
        $this->documentFactory = $documentFactory;
        $this->siterootManager = $siterootManager;
        $this->treeManager = $treeManager;
        $this->elementService = $elementService;
        $this->configurator = $configurator;
        $this->router = $router;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentFactory()
    {
        return $this->documentFactory;
    }

    /**
     * Return document class
     *
     * @return string
     */
    public function getDocumentClass()
    {
        return 'Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument';
    }

    /**
     * Return all identifiers
     *
     * @return array
     */
    public function findIdentifiers()
    {
        $indexIdentifiers = array();

        foreach ($this->treeManager->findAll() as $tree) {
            $siteroot = $this->siterootManager->find($tree->getSiterootId());

            // get siteroot properties
            $isSiterootEnabled = '1' == $siteroot->getProperty('indexer.elements.enabled');
            $skipRestricted    = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
            $skipElementTypes
                = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

            if (!$isSiterootEnabled) {
                // TODO: enable
                //continue;
            }

            $rii = new \RecursiveIteratorIterator(
                $tree->getIterator(),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($rii as $treeNode) {
                /* @var $treeNode TreeNodeInterface */

                if ($tree->isInstance($treeNode) && !$tree->isInstanceMaster($treeNode)) {
                    continue;
                }

                /**
                 * skip specific tids
                 */
                if ($treeNode->getAttribute('searchNoIndex', false)) {
                    continue;
                }

                foreach ($tree->getPublishedVersions($treeNode) as $language => $onlineVersion) {
                    /**
                     * skip restricted, if not globally allowed
                     */
                    if ($skipRestricted && $treeNode->getNeedAuthentication()) {
                        continue;
                    }

                    $element = $this->elementService->findElement($treeNode->getTypeId());

                    /**
                     * skip specific element types
                     */
                    if (in_array($element->getElementtypeId(), $skipElementTypes)) {
                        continue;
                    }

                    $elementtype = $this->elementService->findElementtype($element);
                    if ('full' !== $elementtype->getType()) {
                        // ElementtypeVersion::TYPE_FULL
                        continue;
                    }

                    $id = sprintf('%s_%s_%s', 'treenode', $treeNode->getId(), $language);
                    $indexIdentifiers[$id] = $id;
                }
            }
        }

        return $indexIdentifiers;
    }

    /**
     * Map document
     *
     * @param string $identifier
     *
     * @return DocumentInterface
     */
    public function map($identifier)
    {
        list($prefix, $tid, $language) = explode('_', $identifier);

        $tree = $this->treeManager->findByTreeId($tid);
        if (!$tree) {
            return null;
        }

        $treeNode = $tree->get($tid);
        if (!$treeNode) {
            return null;
        }

        $onlineVersion = $tree->getPublishedVersion($treeNode, $language);
        if (!$onlineVersion) {
            return null;
        }

        $element        = $this->elementService->findElement($treeNode->getTypeId());
        //$elementVersion = $this->elementService->findElementVersion($element, $onlineVersion);
        $elementVersion = $this->elementService->findLatestElementVersion($element);

        if (!$this->isNodeIndexible($treeNode, $language)) {
            return null;
        }

        return $this->mapElementToDocument($treeNode, $elementVersion, $language, $identifier);
    }

    /**
     * Get document
     *
     * @param TreeNodeInterface $treeNode
     * @param ElementVersion    $elementVersion
     * @param string            $language
     * @param integer           $id
     *
     * @return DocumentInterface|false
     * @throws \Exception
     */
    private function mapElementToDocument(
        TreeNodeInterface $treeNode,
        ElementVersion $elementVersion,
        $language,
        $id)
    {
        try {
            ob_start();

            $document = $this->documentFactory->factory($this->getDocumentClass());
            $document->setIdentifier($id);

            $this->handleBoost($document, $treeNode, $elementVersion);
            $result = $this->loadTreeNode($document, $treeNode, $elementVersion, $language);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            if (!$result) {
                $this->logger->info("TreeNode {$treeNode->getId()} not indexed, no result from loadTreeNode()");

                return false;
            }
        } catch (\Exception $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            throw $e;
        }

        $event = new MapDocumentEvent($document, $treeNode, $elementVersion, $language);
        $this->dispatcher->dispatch(IndexerElementEvents::MAP_DOCUMENT, $event);

        return $document;
    }

    /**
     * Parse a multivalue property 123:2;17:3
     *
     * @param string $property
     *
     * @return array
     */
    private function getKeyValueProperty($property)
    {
        $result = array();

        // extract key/value pairs
        $valuePairs = explode(';', $property);
        foreach ($valuePairs as $valuePair) {
            // extract key/value of a single value
            $keyValue = explode(':', $valuePair);

            // key and value must be present
            if (!isset($keyValue[1]) || !isset($keyValue[0])) {
                continue;
            }

            $key   = trim($keyValue[0]);
            $value = trim($keyValue[1]);

            // key and value must be present
            if (!strlen($key) || !strlen($value)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Handle document boost
     *
     * @param DocumentInterface $document
     * @param TreeNodeInterface $treeNode
     * @param ElementVersion    $elementVersion
     */
    private function handleBoost(DocumentInterface $document,
                                 TreeNodeInterface $treeNode,
                                 ElementVersion $elementVersion)
    {
        $siteroot = $this->siterootManager->find($treeNode->getTree()->getSiterootId());

        $boostProperty = $siteroot->getProperty('indexer.elements.boost.tids');
        $boostTids     = $this->getKeyValueProperty($boostProperty);
        $tid           = $treeNode->getId();

        // 1. try boosting by tid
        if (isset($boostTids[$tid])) {
            $document->setBoost($boostTids[$tid]);

            return;
        }

        $boostProperty     = $siteroot->getProperty('indexer.elements.boost.elementtypeids');
        $boostElementtypes = $this->getKeyValueProperty($boostProperty);
        $elementTypeId     = $elementVersion->getElement()->getElementtypeId();

        // 2. try boosting by element type id
        if (isset($boostElementtypes[$elementTypeId])) {
            $document->setBoost($boostElementtypes[$elementTypeId]);
        }
    }

    /**
     * Load a html representation of an element.
     *
     * @param DocumentInterface $document
     * @param TreeNodeInterface $treeNode
     * @param ElementVersion    $elementVersion
     * @param string            $language
     *
     * @return bool
     */
    private function loadTreeNode(DocumentInterface $document,
                                  TreeNodeInterface $treeNode,
                                  ElementVersion $elementVersion,
                                  $language)
    {
        $request = new Request();

        $requestStack = $this->container->get('request_stack');
        $requestStack->push($request);

        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');

        $siteroot = $this->siterootManager->find($treeNode->getTree()->getSiterootId());
        $siterootUrl = $siteroot->getDefaultUrl();

        $request->setLocale($language);
        $request->attributes->set('_locale', $language);
        $request->attributes->set('language', $language);
        $request->attributes->set('routeDocument', $treeNode);
        $request->attributes->set('contentDocument', $treeNode);
        $request->attributes->set('siterootUrl', $siterootUrl);
        $request->attributes->set('preview', true);

        try {
            /* @var $configuration Configuration */
            $configuration = $this->configurator->configure($request, null);
            $data = $configuration->getVariables();

            $content = $this->templating->render($data['template'], (array) $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        $this->container->leaveScope('request');

        // Find Title
        $match = array();
        preg_match('#<h1.*?\>(.*?)\</h1\>#u', $content, $match);

        if (!isset($match[1]) || !trim(strip_tags($match[1]))) {
            preg_match('#<h2.*?\>(.*?)\</h2\>#u', $content, $match);
        }

        if (!isset($match[1]) || !trim(strip_tags($match[1]))) {
            preg_match('#<title.*?\>(.*?)\</title\>#u', $content, $match);
        }

        if (isset($match[1]) && trim(strip_tags($match[1]))) {
            $title = trim(strip_tags($match[1]));
        } else {
            $title = $elementVersion->getPageTitle($language);
        }

        $contentCleaner = new ContentCleaner();
        $content = $contentCleaner->clean($content);

        $url     = $this->router->generate($treeNode, array('language' => $language, 'preview' => true));
        $version = $elementVersion->getVersion();

        $elementtype = $this->elementService->findElementtype($elementVersion->getElement());
        $elementtypeUniqueId = $elementtype->getUniqueId();

        $document->setValue('language', $language);
        $document->setValue('title', $title);
        $document->setValue('content', $content);
        $document->setValue('url', $url);
        $document->setValue('tid', $treeNode->getId());
        $document->setValue('eid', $treeNode->getTypeId());
        $document->setValue('elementtypeId', $elementtype->getId());
        $document->setValue('elementtype', $elementtypeUniqueId);
        $document->setValue('siterootId', $treeNode->getTree()->getSiterootId());
        $document->setValue('siteroot', $configuration->get('siteroot')->getTitle($language));
        $document->setValue('navigation', $treeNode->getInNavigation() ? true : false);
        $document->setValue('restricted', $treeNode->getNeedAuthentication() ? true : false);

        return true;
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @param string            $language
     *
     * @return bool
     */
    public function isNodeIndexible(TreeNodeInterface $treeNode, $language)
    {
        $siterootId = $treeNode->getTree()->getSiterootId();

        $tree = $treeNode->getTree();
        $siteroot = $this->siterootManager->find($siterootId);
        $isSiterootEnabled  = '1' == $siteroot->getProperty('indexer.elements.enabled');
        $skipRestricted = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
        $skipElementTypeIds = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

        // TODO: remove
        $isSiterootEnabled = true;

        // skip siteroot?
        if (!$isSiterootEnabled) {
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, siteroot disabled");

            return false;
        }

        // skip tid?
        if ($treeNode->getAttribute('searchNoIndex', false)) {
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, treeNode is marked with no-index");

            return false;
        }

        // skip restricted?
        if ($skipRestricted && $treeNode->getNeedAuthentication()) {
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, treeNode needs authentication");

            return false;
        }

        // skip elementtype?
        $element       = $this->elementService->findElement($treeNode->getTypeId());
        $elementtypeId = $element->getElementtypeId();
        if (in_array($elementtypeId, $skipElementTypeIds)) {
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, elementtype id is on skip list");

            return false;
        }

        $skipElementtypeIds = array(
            'ca14d613-7f4b-225c-7973-a18a7098cbe7',
            'f70c47e1-137e-465a-ac24-92c285619f29',
            'ca14d613-7f4b-225c-7973-a18a7098cbe7'
        );
        if (in_array($elementtypeId, $skipElementtypeIds)) {
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, elementtype id is on skip list");

            return false;
        }

        // skip non full elements
        $elementtype     = $this->elementService->findElementtype($element);
        if ('full' !== $elementtype->getType()) {
            // ElementtypeVersion::TYPE_FULL
            $this->logger->info("TreeNode {$treeNode->getId()} not indexed, not a full element");

            return false;
        }

        return true;
    }
}
