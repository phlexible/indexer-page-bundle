<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\EventListener;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementIndexer;
use Phlexible\Bundle\TreeBundle\Event\DeleteNodeEvent;
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
     * @var ElementIndexer
     */
    private $indexer;

    /**
     * @var array
     */
    private $languages;

    /**
     * @param ElementIndexer $indexer
     * @param string         $languages
     */
    public function __construct(ElementIndexer $indexer, $languages)
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
        $node       = $event->getNode();
        $language   = $event->getLanguage();

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
        $language   = $event->getLanguage();
        $node       = $event->getNode();

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
