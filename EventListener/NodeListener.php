<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementBundle\EventListener;

/**
 * Node listener
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class NodeListener
{
    public function onPublishNode(PublishNodeEvent $event, array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->queueUpdate($node, array($language));
    }

    public function onUpdateNode(UpdateNodeEvent $event, array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        // global values (context, restricted) may be changed
        // -> reindex all languages
        $indexerElementsTools->queueUpdate($node);
    }

    public function onMoveNode(MoveNodeEvent $event, array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();
        $indexerElementsTools->queueUpdate($node);
    }

    public function onSetNodeOffline(SetNodeOfflineEvent $event, array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->remove($node, array($language));
    }

    public function onDeleteNode(DeleteNodeEvent $event,
                                        array $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container            = $params['container'];
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        $indexerElementsTools->remove($node);
    }
}
