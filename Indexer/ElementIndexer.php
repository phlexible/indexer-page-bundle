<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementBundle\Indexer;

use Doctrine\ORM\EntityManager;
use Phlexible\IndexerBundle\Indexer\AbstractIndexer;
use Phlexible\IndexerBundle\Storage\StorageInterface;
use Phlexible\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\IndexerElementBundle\IndexerElementEvents;
use Phlexible\TreeBundle\TreeManager;
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TreeManager
     */
    private $treeManager;

    /**
     * @var ElementManager
     */
    private $elementManager;

    /**
     * @var ElementVersionManager
     */
    private $elementVersionManager;

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
     * @param EntityManager            $entityManager
     * @param TreeManager              $treeManager
     * @param ElementManager           $elementManager
     * @param ElementVersionManager    $elementVersionManager
     * @param ContextManager           $contextManager
     * @param string                   $requestHandler
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                StorageInterface $storage,
                                EntityManager $entityManager,
                                TreeManager $treeManager,
                                ElementManager $elementManager,
                                ElementVersionManager $elementVersionManager,
                                ContextManager $contextManager,
                                $requestHandler)
    {
        $this->dispatcher            = $dispatcher;
        $this->storage               = $storage;
        $this->entityManager         = $entityManager;
        $this->treeManager           = $treeManager;
        $this->elementManager        = $elementManager;
        $this->elementVersionManager = $elementVersionManager;
        $this->contextManager        = $contextManager;
        $this->requestHandler        = $requestHandler;
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

        foreach ($this->siterootRepository->getAllSiteRoots() as $siteroot)
        {
            /* @var $siteroot Siteroot */

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

            foreach ($rii as $treeNode) /* @var $treeNode Makeweb_Elements_Tree_Node */
            {
                if ($treeNode->isInstance() && !$treeNode->isInstanceMaster())
                {
                    continue;
                }

                /**
                 * skip specific tids
                 */
                if (in_array($treeNode->getId(), $skipTids))
                {
                    continue;
                }

                foreach ($treeNode->getOnlineLanguages() as $language)
                {
                    $onlineVersion = $treeNode->getOnlineVersion($language);
                    $eid           = $treeNode->getEid();

                    /**
                     * skip restricted, if not globally allowed
                     */
                    if ($skipRestricted && $treeNode->isRestricted($onlineVersion))
                    {
                        continue;
                    }

                    $elementVersion = $this->elementVersionManager->get($eid, $onlineVersion);

                    /**
                     * skip specific element types
                     */
                    if (in_array($elementVersion->getElementTypeID(), $skipElementTypes))
                    {
                        continue;
                    }

                    $elementTypeVersion = $elementVersion->getElementTypeVersionObj();
                    $elementType        = $elementTypeVersion->getElementType();
                    $elementTypeName    = $elementType->getType();
                    if (Makeweb_Elementtypes_Elementtype_Version::TYPE_FULL !== $elementTypeName)
                    {
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
     * @return MWF_Core_Indexer_Document
     */
    public function getDocumentByIdentifier($id)
    {
        list($prefix, $tid, $language) = explode('_', $id);

        $treeNode       = $this->treeManager->getNodeByNodeId($tid);
        $eid            = $treeNode->getEid();
        $onlineVersion  = $treeNode->getOnlineVersion($language);
        $elementVersion = $this->elementVersionManager->get($eid, $onlineVersion);

        if (!$this->isIndexibleNode($treeNode, $language))
        {
            return null;
        }

        return $this->_mapElementToDocument($treeNode, $elementVersion, $language, $id);
    }

    /**
     * Get document
     *
     * @param Makeweb_Elements_Tree_Node       $treeNode
     * @param Makeweb_Elements_Element_Version $elementVersion
     * @param string                           $language
     * @param integer                          $id
     * @return MWF_Core_Indexer_Document|false
     * @throws Exception
     */
    protected function _mapElementToDocument(Makeweb_Elements_Tree_Node $treeNode,
                                             Makeweb_Elements_Element_Version $elementVersion,
                                             $language,
                                             $id)
    {
        try
        {
            ob_start();

            $document = $this->createDocument();
            $document->setIdentifier($id);

            $this->_handleBoost($document, $treeNode, $elementVersion);
            $result = $this->_loadTreeNode($document, $treeNode, $elementVersion, $language);

            while (ob_get_level() > 0)
            {
                ob_end_clean();
            }

            if (!$result)
            {
                MWF_Log::warn('Create document failed.');
                return false;
            }
        }
        catch (\Exception $e)
        {
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
    protected function _getKeyValueProperty($property)
    {
        $result = array();

        // extract key/value pairs
        $valuePairs = explode(';', $property);
        foreach ($valuePairs as $valuePair)
        {
            // extract key/value of a single value
            $keyValue = explode(':', $valuePair);

            // key and value must be present
            if (!isset($keyValue[1]) || !isset($keyValue[0]))
            {
                continue;
            }

            $key   = trim($keyValue[0]);
            $value = trim($keyValue[1]);

            // key and value must be present
            if (!strlen($key) || !strlen($value))
            {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Handle document boost
     *
     * @param MWF_Core_Indexer_Document_Interface $document
     * @param Makeweb_Elements_Tree_Node          $treeNode
     * @param Makeweb_Elements_Element_Version    $versionObj
     */
    protected function _handleBoost(MWF_Core_Indexer_Document_Interface $document,
                                    Makeweb_Elements_Tree_Node $treeNode,
                                    Makeweb_Elements_Element_Version $elementVersion)
    {
        $siteroot = $this->siterootRepository->getById($treeNode->getSiteRootId());

        $boostProperty = $siteroot->getProperty('indexer.elements.boost.tids');
        $boostTids     = $this->_getKeyValueProperty($boostProperty);
        $tid           = $treeNode->getId();

        // 1. try boosting by tid
        if (isset($boostTids[$tid]))
        {
            $document->setBoost($boostTids[$tid]);
            return;
        }

        $boostProperty     = $siteroot->getProperty('indexer.elements.boost.elementtypeids');
        $boostElementtypes = $this->_getKeyValueProperty($boostProperty);
        $elementTypeId     = $elementVersion->getElementTypeID();

        // 2. try boosting by element type id
        if (isset($boostElementtypes[$elementTypeId]))
        {
            $document->setBoost($boostElementtypes[$elementTypeId]);
            return;
        }
    }

    /**
     * Load a html representation of an element.
     *
     * @param MWF_Core_Indexer_Document_Interface $document
     * @param Makeweb_Elements_Tree_Node          $treeNode
     * @param Makeweb_Elements_Element_Version    $versionObj
     * @param string                              $language
     */
    protected function _loadTreeNode(MWF_Core_Indexer_Document_Interface $document,
                                     Makeweb_Elements_Tree_Node $treeNode,
                                     Makeweb_Elements_Element_Version $elementVersion,
                                     $language)
    {
        $response = new Zend_Controller_Response_Http();
        $request = new Makeweb_Frontend_Request($response, false, false);
        $request->setVersionOnline();

        $requestHandlerClass = $this->requestHandler;

        if ($requestHandlerClass)
        {
            $request->setHandler(new $requestHandlerClass());
        }

        $tid = $treeNode->getId();

        $request->setLanguage($language);
        $request->setTid($tid);
        $request->setSiteRootId($treeNode->getSiteRootId());

        $useContext = $this->contextManager->useContext();

        if ($useContext)
        {
            $countries = $request->getContext()->getCountriesForTidAndLanguage($tid, $language);
            if (!count($countries))
            {
                $countries[] = 'global';
            }

            if ($useContext)
            {
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

        $doc = Zend_Search_Lucene_Document_Html::loadHTML($html, false, 'UTF-8');

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

    public function isIndexibleNode(Makeweb_Elements_Tree_Node $node, $language)
    {
        $tid        = $node->getId();
        $siterootId = $node->getSiteRootId();

        $siteroot           = $this->siterootRepository->getById($siterootId);
        $isSiterootEnabled  = '1' == $siteroot->getProperty('indexer.elements.enabled');
        $skipRestricted     = '1' == $siteroot->getProperty('indexer.elements.skip.restricted');
        $skipTids           = explode(';', $siteroot->getProperty('indexer.elements.skip.tids'));
        $skipElementTypeIds = explode(';', $siteroot->getProperty('indexer.elements.skip.elementtypeids'));

        // skip siteroot?
        if (!$isSiterootEnabled)
        {
            return false;
        }

        // skip tid?
        if (in_array($tid, $skipTids))
        {
            return false;
        }

        // skip restricted?
        if ($skipRestricted)
        {
            $version      = $node->getOnlineVersion($language);
            $isRestricted = $node->isRestricted($version);

            if ($isRestricted)
            {
                return false;
            }
        }

        // skip elementtype?
        $eid           = $node->getEid();
        $element       = $this->elementManager->getByEID($eid);
        $elementTypeId = $element->getElementTypeId();
        if (in_array($elementTypeId, $skipElementTypeIds))
        {
            return false;
        }

        // skip non full elements
        $elementType     = $element->getElementType();
        $elementTypeType = $elementType->getType();
        if (Makeweb_Elementtypes_Elementtype_Version::TYPE_FULL !== $elementTypeType)
        {
            return false;
        }

        return true;
    }
}
