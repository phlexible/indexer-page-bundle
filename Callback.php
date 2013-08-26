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
 * Elements Indexer Callback
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Marco Fischer <mf@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_IndexerElements_Callback
{
    public static function onCreateDocument(MWF_Core_Indexer_Event_CreateDocument $event)
    {
        $document = $event->getDocument();


        if ('elements' !== $document->getDocumentType())
        {
            return;
        }

        $document->setFields(
            array(
                'title'           => array(),
                'highlight_title' => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY),
                'tags'            => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'copy'            => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),

                'language'        => array(),
                'context'         => array(MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'cleantitle'      => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY),
                'tid'             => array(),
                'eid'             => array(),
                'elementtype'     => array(),
                'url'             => array(),
                'siteroot'        => array(),
                'restricted'      => array(),
                'content'         => array(MWF_Core_Indexer_Document_Interface::CONFIG_COPY),
            )
        );
    }

    public static function onPublishNode(Makeweb_Elements_Event_PublishNode $event,
                                         array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->queueUpdate($node, array($language));
    }

    public static function onUpdateNode(Makeweb_Elements_Event_UpdateNode $event,
                                        array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        // global values (context, restricted) may be changed
        // -> reindex all languages
        $indexerElementsTools->queueUpdate($node);
    }

    public static function onMoveNode(Makeweb_Elements_Event_MoveNode $event,
                                      array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();
        $indexerElementsTools->queueUpdate($node);
    }

    public static function onSetNodeOffline(Makeweb_Elements_Event_SetNodeOffline $event,
                                            array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->remove($node, array($language));
    }

    public static function onDeleteNode(Makeweb_Elements_Event_DeleteNode $event,
                                        array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        $indexerElementsTools->remove($node);
    }
}
