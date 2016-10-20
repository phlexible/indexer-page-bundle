<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\EventListener;

use Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer;
use Phlexible\Bundle\TreeBundle\Event\DeleteNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\MoveNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\NodeEvent;
use Phlexible\Bundle\TreeBundle\Event\PublishNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\SetNodeOfflineEvent;
use Phlexible\Bundle\TreeBundle\TreeEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Node listener.
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class NodeListener implements EventSubscriberInterface
{
    /**
     * @var PageIndexer
     */
    private $indexer;

    /**
     * @var array
     */
    private $languages;

    /**
     * @param PageIndexer $indexer
     * @param string      $languages
     */
    public function __construct(PageIndexer $indexer, $languages)
    {
        $this->indexer = $indexer;
        $this->languages = explode(',', $languages);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            TreeEvents::PUBLISH_NODE => 'onPublishNode',
            TreeEvents::UPDATE_NODE => 'onUpdateNode',
            TreeEvents::MOVE_NODE => 'onMoveNode',
            TreeEvents::SET_NODE_OFFLINE => 'onSetNodeOffline',
            TreeEvents::BEFORE_DELETE_NODE => 'onDeleteNode',
        );
    }

    /**
     * @param PublishNodeEvent $event
     */
    public function onPublishNode(PublishNodeEvent $event)
    {
        $node = $event->getNode();
        $language = $event->getLanguage();

        $this->indexer->addNode($node, $language, true);
    }

    /**
     * @param NodeEvent $event
     */
    public function onUpdateNode(NodeEvent $event)
    {
        $node = $event->getNode();

        foreach ($node->getTree()->getPublishedLanguages($node) as $language) {
            $this->indexer->addNode($node, $language, true);
        }
    }

    /**
     * @param MoveNodeEvent $event
     */
    public function onMoveNode(MoveNodeEvent $event)
    {
        $node = $event->getNode();

        foreach ($node->getTree()->getPublishedLanguages($node) as $language) {
            $this->indexer->addNode($node, $language, true);
        }
    }

    /**
     * @param SetNodeOfflineEvent $event
     */
    public function onSetNodeOffline(SetNodeOfflineEvent $event)
    {
        $language = $event->getLanguage();
        $node = $event->getNode();

        $this->indexer->deleteNode($node, $language, true);
    }

    /**
     * @param DeleteNodeEvent $event
     */
    public function onDeleteNode(DeleteNodeEvent $event)
    {
        $node = $event->getNode();

        foreach ($this->languages as $language) {
            $this->indexer->deleteNode($node, $language, true);
        }
    }
}
