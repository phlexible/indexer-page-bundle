<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\EventListener;

use Phlexible\Bundle\TreeBundle\Event\MoveNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\NodeEvent;
use Phlexible\Bundle\TreeBundle\Event\PublishNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\SetNodeOfflineEvent;
use Phlexible\Bundle\TreeBundle\TreeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Node listener
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class NodeListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            TreeEvents::PUBLISH_NODE     => 'onPublishNode',
            TreeEvents::UPDATE_NODE      => 'onUpdateNode',
            TreeEvents::MOVE_NODE        => 'onMoveNode',
            TreeEvents::SET_NODE_OFFLINE => 'onSetNodeOffline',
            TreeEvents::DELETE_NODE      => 'onDeleteNode',
        );
    }

    /**
     * @param PublishNodeEvent $event
     */
    public function onPublishNode(PublishNodeEvent $event)
    {
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->queueUpdate($node, array($language));
    }

    /**
     * @param NodeEvent $event
     */
    public function onUpdateNode(NodeEvent $event)
    {
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        // global values (context, restricted) may be changed
        // -> reindex all languages
        $indexerElementsTools->queueUpdate($node);
    }

    /**
     * @param MoveNodeEvent $event
     */
    public function onMoveNode(MoveNodeEvent $event)
    {
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();
        $indexerElementsTools->queueUpdate($node);
    }

    /**
     * @param SetNodeOfflineEvent $event
     */
    public function onSetNodeOffline(SetNodeOfflineEvent $event)
    {
        $indexerElementsTools = $container->indexerElementsTools;

        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $indexerElementsTools->remove($node, array($language));
    }

    /**
     * @param NodeEvent $event
     */
    public function onDeleteNode(NodeEvent $event)
    {
        $indexerElementsTools = $container->indexerElementsTools;

        $node = $event->getNode();

        $indexerElementsTools->remove($node);
    }
}
