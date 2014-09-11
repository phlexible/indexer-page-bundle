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
use Phlexible\Bundle\ElementRendererBundle\DataProvider\DataProvider;
use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\AbstractIndexer;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerElementBundle\IndexerElementEvents;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Element indexer
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class ElementIndexer extends AbstractIndexer
{
    /**
     * @var string
     */
    const DOCUMENT_TYPE = 'elements';

    /**
     * @var StorageInterface
     */
    private $storage;

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
     * @var DataProvider
     */
    private $dataProvider;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param StorageInterface            $storage
     * @param DocumentFactory             $documentFactory
     * @param SiterootManagerInterface    $siterootManager
     * @param ContentTreeManagerInterface $treeManager
     * @param ElementService              $elementService
     * @param DataProvider                $dataProvider
     * @param RouterInterface             $router
     * @param EngineInterface             $templating
     * @param EventDispatcherInterface    $dispatcher
     * @param ContainerInterface          $container
     */
    public function __construct(
        StorageInterface $storage,
        DocumentFactory $documentFactory,
        SiterootManagerInterface $siterootManager,
        ContentTreeManagerInterface $treeManager,
        ElementService $elementService,
        DataProvider $dataProvider,
        RouterInterface $router,
        EngineInterface $templating,
        EventDispatcherInterface $dispatcher,
        ContainerInterface $container)
    {
        $this->storage = $storage;
        $this->documentFactory = $documentFactory;
        $this->siterootManager = $siterootManager;
        $this->treeManager = $treeManager;
        $this->elementService = $elementService;
        $this->dataProvider = $dataProvider;
        $this->router = $router;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'Elements indexer';
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        return $this->storage;
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
        return 'Phlexible\Bundle\IndexerBundle\Document\Document';
    }

    /**
     * Return document type
     *
     * @return string
     */
    public function getDocumentType()
    {
        return self::DOCUMENT_TYPE;
    }

    /**
     * Return all identifiers
     *
     * @return array
     */
    public function getAllIdentifiers()
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
                continue;
            }

            $siterootId = $siteroot->getId();

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
                    $eid = $treeNode->getTypeId();

                    /**
                     * skip restricted, if not globally allowed
                     */
                    if ($skipRestricted && $treeNode->getNeedAuthentication())
                    {
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
                    if ('full' !== $elementtype->getType()) { // ElementtypeVersion::TYPE_FULL
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
     * Get document by identifier
     *
     * @param string $id
     *
     * @return DocumentInterface
     */
    public function getDocumentByIdentifier($id)
    {
        list($prefix, $tid, $language) = explode('_', $id);

        $tree           = $this->treeManager->findByTreeId($tid);
        $treeNode       = $tree->get($tid);
        $onlineVersion  = $tree->getPublishedVersion($treeNode, $language);
        if (!$onlineVersion) {
            return null;
        }

        $element        = $this->elementService->findElement($treeNode->getTypeId());
        //$elementVersion = $this->elementService->findElementVersion($element, $onlineVersion);
        $elementVersion = $this->elementService->findLatestElementVersion($element);

        if (!$this->isIndexibleNode($treeNode, $language)) {
            return null;
        }

        return $this->mapElementToDocument($treeNode, $elementVersion, $language, $id);
    }

    /**
     * Get document
     *
     * @param TreeNodeInterface $treeNode
     * @param ElementVersion    $elementVersion
     * @param string            $language
     * @param integer           $id
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

            $document = $this->createDocument();
            $document->setIdentifier($id);

            $this->handleBoost($document, $treeNode, $elementVersion);
            $result = $this->loadTreeNode($document, $treeNode, $elementVersion, $language);

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            if (!$result) {
                return false;
            }
        } catch (\Exception $e) {
            while (ob_get_level() > 0)
            {
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
        if (isset($boostTids[$tid]))
        {
            $document->setBoost($boostTids[$tid]);
            return;
        }

        $boostProperty     = $siteroot->getProperty('indexer.elements.boost.elementtypeids');
        $boostElementtypes = $this->getKeyValueProperty($boostProperty);
        $elementTypeId     = $elementVersion->getElement()->getElementtypeId();

        // 2. try boosting by element type id
        if (isset($boostElementtypes[$elementTypeId])) {
            $document->setBoost($boostElementtypes[$elementTypeId]);
            return;
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

        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');

        $siteroot = $this->siterootManager->find($treeNode->getTree()->getSiterootId());
        $siteroot->setContentChannels(array(1 => 1));
        $siterootUrl = $siteroot->getDefaultUrl();

        $request->attributes->set('language', $language);
        $request->attributes->set('routeDocument', $treeNode);
        $request->attributes->set('contentDocument', $treeNode);
        $request->attributes->set('siterootUrl', $siterootUrl);
        $request->attributes->set('preview', true);

        $data = $this->dataProvider->provide($request);
        //$content = $this->templating->render($data['template'], (array) $data);
        $content = $this->templating->render('test.html.twig', (array) $data);

        $this->container->leaveScope('request');

        // Remove Content between NoIndex tags
        $content = preg_replace("|<!--\s*NoIndex\s*-->(.*)<!--\s*/NoIndex\s*-->|Umsu", '', $content);

        // strip_tags may concatenate word which are logically separated
        // <ul><li>one</li><li>two</li></ul> -> onetwo
        $content = str_replace('<', ' <', $content);

        // Remove NL, CR, TABs
        $content = str_replace(array("\r", "\n", "\t"), array(' ',' ',' '), $content);

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);

        // Convert special chars to HTML-readable stuff
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        // Find Title
        $title = '';
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

        $doc = \Zend_Search_Lucene_Document_Html::loadHTML($content, false, 'UTF-8');

        $content = $doc->getFieldUtf8Value('body');

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $title   = html_entity_decode($title, ENT_QUOTES, 'UTF-8');

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);
        $title   = preg_replace('|\s+|u', ' ', $title);

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
        $document->setValue('elementtype', $elementtypeUniqueId);
        $document->setValue('siteroot', $treeNode->getTree()->getSiterootId());
        $document->setValue('restricted', $treeNode->getNeedAuthentication() ? '1' : '0');

        return true;
    }

    /**
     * @param TreeNodeInterface $treeNode
     * @param string            $language
     *
     * @return bool
     */
    public function isIndexibleNode(TreeNodeInterface $treeNode, $language)
    {
        $siterootId = $treeNode->getTree()->getSiterootId();

        $tree = $treeNode->getTree();
        $siteroot = $this->siterootManager->find($siterootId);
        $isSiterootEnabled  = '1' == $siteroot->getProperty('indexer.elements.enabled');
        $skipRestricted = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
        $skipElementTypeIds = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

        // skip siteroot?
        if (!$isSiterootEnabled) {
            return false;
        }

        // skip tid?
        if ($treeNode->getAttribute('searchNoIndex', false)) {
            return false;
        }

        // skip restricted?
        if ($skipRestricted) {
            $isRestricted = $treeNode->getNeedAuthentication();

            if ($isRestricted) {
                return false;
            }
        }

        // skip elementtype?
        $element       = $this->elementService->findElement($treeNode->getTypeId());
        $elementtypeId = $element->getElementtypeId();
        if (in_array($elementtypeId, $skipElementTypeIds)) {
            return false;
        }

        // skip non full elements
        $elementtype     = $this->elementService->findElementtype($element);
        if ('full' !== $elementtype->getType()) { // ElementtypeVersion::TYPE_FULL
            return false;
        }

        return true;
    }
}
