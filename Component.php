<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Elements Indexer Component
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Marco Fischer <mf@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_IndexerElements_Component extends MWF_Component_Abstract
{
    /**
     * Constructor
     * Initialses the Component values
     */
    public function __construct()
    {
        $this->setVersion('0.7.1');
        $this->setId('indexerelements');
        $this->setFile(__FILE__);
        $this->setPackage('makeweb');
    }

    public function initContainer(MWF_Container_ContainerBuilder $container)
    {
        $container->addComponents(
            array(
                'indexerElementsIndexer' => array(
                    'class' => 'Makeweb_IndexerElements_Indexer',
                    'arguments' => array(
                        'dispatcher',
                        'indexerDocumentFactory',
                        'siterootManager',
                        'elementsTreeManager',
                        'elementsManager',
                        'elementsVersionManager',
                        'elementsContextmanager',
                        ':frontend.request.handler'
                    ),
                    'scope' => 'singleton',
                ),
                'indexerElementsTools' => array(
                    'class' => 'Makeweb_IndexerElements_Tools',
                    'arguments' => array(
                        'queueManager',
                        'indexerTools',
                        'indexerElementsIndexer',
                    ),
                    'scope' => 'singleton',
                ),
                'indexerElementsQueryEid' => array(
                    'class'     => 'Makeweb_IndexerElements_Query_Eid',
                    'arguments' => array('indexerQueryParser'),
                    'scope'     => 'prototype',
                ),
                // listener
                'indexerElementsListenerCreateDocument' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => MWF_Core_Indexer_Event::CREATE_DOCUMENT,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onCreateDocument'),
                    ),
                ),
                'indexerElementsListenerPublishNode' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Makeweb_Elements_Event::PUBLISH_NODE,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onPublishNode'),
                    ),
                ),
                'indexerElementsListenerUpdateNode' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Makeweb_Elements_Event::UPDATE_NODE,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onUpdateNode'),
                    ),
                ),
                'indexerElementsListenerMoveNode' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Makeweb_Elements_Event::MOVE_NODE,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onMoveNode'),
                    ),
                ),
                'indexerElementsListenerSetNodeOffline' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Makeweb_Elements_Event::SET_NODE_OFFLINE,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onMoveNode'),
                    ),
                ),
                'indexerElementsListenerDeleteNode' => array(
                    'tag' => array(
                        'name' => 'event.listener',
                        'event' => Makeweb_Elements_Event::DELETE_NODE,
                        'callback' => array('Makeweb_IndexerElements_Callback', 'onDeleteNode'),
                    ),
                ),
            )
        );
    }

    public function getIndexerSearches()
    {
        return array('indexer-elements-query-eid' => 'indexerElementsQueryEid');
    }

    public function getIndexers()
    {
        return array('elements' => 'indexerElementsIndexer');
    }

    public function getSiterootProperties()
    {
        // @TODO: implement skip/boost for unique IDs

        return array(
            'indexer.elements.enabled',
            'indexer.elements.skip.restricted',
            'indexer.elements.skip.elementtypeids',
            'indexer.elements.skip.tids',
            'indexer.elements.boost.elementtypeids',
            'indexer.elements.boost.tids',
        );
    }
}
