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
use Phlexible\Bundle\ElementtypeBundle\Entity\ElementtypeVersion;
use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\AbstractIndexer;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerElementBundle\IndexerElementEvents;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Phlexible\Bundle\TreeBundle\Tree\TreeManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

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
     * @var TreeManager
     */
    private $treeManager;

    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var ContextManager
     */
    private $contextManager;

    /**
     * @var string
     */
    private $requestHandler;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param StorageInterface         $storage
     * @param DocumentFactory          $documentFactory
     * @param SiterootManagerInterface $siterootManager
     * @param TreeManager              $treeManager
     * @param ElementService           $elementService
     * @param ContextManager           $contextManager
     * @param string                   $requestHandler
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                StorageInterface $storage,
                                DocumentFactory $documentFactory,
                                SiterootManagerInterface $siterootManager,
                                TreeManager $treeManager,
                                ElementService $elementService,
                                ContextManager $contextManager,
                                $requestHandler)
    {
        $this->dispatcher      = $dispatcher;
        $this->storage         = $storage;
        $this->documentFactory = $documentFactory;
        $this->siterootManager   = $siterootManager;
        $this->treeManager     = $treeManager;
        $this->elementService  = $elementService;
        $this->contextManager  = $contextManager;
        $this->requestHandler  = $requestHandler;
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
        return 'MWF_Core_Indexer_Document';
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

        foreach ($this->siterootManager->findAll() as $siteroot) {
            // get siteroot properties
            $isSiterootEnabled = '1' == $siteroot->getProperty('indexer.elements.enabled');
            $skipRestricted    = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
            $skipTids          = explode(';', $siteroot->getProperty('indexer.elements.skip.tids'));
            $skipElementTypes
                = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

            if (!$isSiterootEnabled)
            {
                continue;
            }

            $siterootId = $siteroot->getId();
            $tree       = $this->treeManager->getBySiteRootId($siterootId);

            $rii = new \RecursiveIteratorIterator(
                $tree->getIterator(),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($rii as $treeNode) {
                /* @var $treeNode TreeNodeInterface */

                if ($treeNode->isInstance() && !$treeNode->isInstanceMaster()) {
                    continue;
                }

                /**
                 * skip specific tids
                 */
                if (in_array($treeNode->getId(), $skipTids)) {
                    continue;
                }

                foreach ($treeNode->getOnlineLanguages() as $language) {
                    $onlineVersion = $treeNode->getOnlineVersion($language);
                    $eid           = $treeNode->getEid();

                    /**
                     * skip restricted, if not globally allowed
                     */
                    if ($skipRestricted && $treeNode->isRestricted($onlineVersion))
                    {
                        continue;
                    }

                    $element = $this->elementService->findElement($eid);

                    /**
                     * skip specific element types
                     */
                    if (in_array($element->getElementtypeId(), $skipElementTypes)) {
                        continue;
                    }

                    $elementtype = $this->elementService->findElementtype($element);
                    if (ElementtypeVersion::TYPE_FULL !== $elementtype->getType()) {
                        continue;
                    }

                    $id = 'treenode_' . $treeNode->getId() . '_' . $language;
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

        $treeNode       = $this->treeManager->getByNodeId($tid)->get($tid);
        $eid            = $treeNode->getTypeId();
        $onlineVersion  = $treeNode->getOnlineVersion($language);
        $element        = $this->elementService->findElement($eid);
        $elementVersion = $this->elementService->findElementVersion($element, $onlineVersion);

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
    private function mapElementToDocument(TreeNodeInterface $treeNode,
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
                MWF_Log::warn('Create document failed.');
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
        $response = new Zend_Controller_Response_Http();
        $request = new Makeweb_Frontend_Request($response, false, false);
        $request->setVersionOnline();

        $requestHandlerClass = $this->requestHandler;

        if ($requestHandlerClass) {
            $request->setHandler(new $requestHandlerClass());
        }

        $tid = $treeNode->getId();

        $request->setLanguage($language);
        $request->setTid($tid);
        $request->setSiteRootId($treeNode->getSiteRootId());

        $useContext = $this->contextManager->useContext();

        if ($useContext) {
            $countries = $request->getContext()->getCountriesForTidAndLanguage($tid, $language);
            if (!count($countries)) {
                $countries[] = 'global';
            }

            if ($useContext) {
                $request->getContext()->setCountry($countries[0]);
            }
        }

        /* @var $renderer Makeweb_Renderers_Html */
        $renderer = $request->getContentChannel()->getRenderer();
        $renderer->setRequest($request);

        $renderer->setResponse(new Zend_Controller_Response_Cli());
        $renderer->render();

        $html = $renderer->getOutput();

        // Remove Content between udmComments
        $html = preg_replace("|<!--\s*UdmComment\s*-->(.*)<!--\s*/UdmComment\s*-->|Umsu", '', $html);
        $html = preg_replace("|<!--\s*NoIndex\s*-->(.*)<!--\s*/NoIndex\s*-->|Umsu", '', $html);

        // strip_tags may concatenate word which are logically separated
        // <ul><li>one</li><li>two</li></ul> -> onetwo
        $html = str_replace('<', ' <', $html);

        // Remove NL, CR, TABs
        $html = str_replace(array("\r", "\n", "\t"), array(' ',' ',' '), $html);

        // Remove multiple whitespaces
        $html = preg_replace('|\s+|u', ' ', $html);

        // Convert special chars to HTML-readable stuff
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        // Find Title
        $title = '';
        $match = array();
        preg_match('#<h1.*?\>(.*?)\</h1\>#u', $html, $match);

        if (!isset($match[1]) || !trim(strip_tags($match[1])))
        {
            preg_match('#<h2.*?\>(.*?)\</h2\>#u', $html, $match);
        }

        if (!isset($match[1]) || !trim(strip_tags($match[1])))
        {
            preg_match('#<title.*?\>(.*?)\</title\>#u', $html, $match);
        }

        if (isset($match[1]) || !trim(strip_tags($match[1])))
        {
            $title = trim(strip_tags($match[1]));
        }
        else
        {
            $title = $elementVersion->getPageTitle($language);
        }

        $doc = \Zend_Search_Lucene_Document_Html::loadHTML($html, false, 'UTF-8');

        $content = $doc->getFieldUtf8Value('body');

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $title   = html_entity_decode($title, ENT_QUOTES, 'UTF-8');

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);
        $title   = preg_replace('|\s+|u', ' ', $title);

        $url     = Makeweb_Navigations_Link::createFromTid($tid, $language, false);
        $version = $elementVersion->getVersion();

        $elementTypeVersionObj = $elementVersion->getElementTypeVersionObj();
        $elementType           = $elementTypeVersionObj->getElementType();
        $elementTypeUniqueId   = $elementType->getUniqueId();

        $document->setValue('language', $language);
        $document->setValue('context', $useContext ? $countries : array());

        $document->setValue('title', $title);
        $document->setValue('content', $content);
        $document->setValue('url', $url);
        $document->setValue('tid', $tid);
        $document->setValue('eid', $treeNode->getEid());
        $document->setValue('elementtype', $elementTypeUniqueId);
        $document->setValue('siteroot', $treeNode->getSiteRootId());
        $document->setValue('restricted', $treeNode->isRestricted($version) ? '1' : '0');

        return true;
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     *
     * @return bool
     */
    public function isIndexibleNode(TreeNodeInterface $node, $language)
    {
        $tid        = $node->getId();
        $siterootId = $node->getTree()->getSiterootId();

        $siteroot           = $this->siterootManager->find($siterootId);
        $isSiterootEnabled  = '1' == $siteroot->getProperty('indexer.elements.enabled');
        $skipRestricted     = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
        $skipTids           = explode(';', $siteroot->getProperty('indexer.elements.skip.tids'));
        $skipElementTypeIds = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

        // skip siteroot?
        if (!$isSiterootEnabled) {
            return false;
        }

        // skip tid?
        if (in_array($tid, $skipTids)) {
            return false;
        }

        // skip restricted?
        if ($skipRestricted) {
            $version      = $node->getOnlineVersion($language);
            $isRestricted = $node->isRestricted($version);

            if ($isRestricted) {
                return false;
            }
        }

        // skip elementtype?
        $eid           = $node->getTypeId();
        $element       = $this->elementService->findElement($eid);
        $elementtypeId = $element->getElementtypeId();
        if (in_array($elementtypeId, $skipElementTypeIds)) {
            return false;
        }

        // skip non full elements
        $elementType     = $element->getElementType();
        $elementTypeType = $elementType->getType();
        if (ElementtypeVersion::TYPE_FULL !== $elementTypeType)
        {
            return false;
        }

        return true;
    }
}
