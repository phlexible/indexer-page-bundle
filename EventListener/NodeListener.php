<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\EventListener;

use Phlexible\Bundle\QueueBundle\Entity\Job;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\TreeBundle\Event\MoveNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\NodeEvent;
use Phlexible\Bundle\TreeBundle\Event\PublishNodeEvent;
use Phlexible\Bundle\TreeBundle\Event\SetNodeOfflineEvent;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
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
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @param JobManagerInterface $jobManager
     */
    public function __construct(JobManagerInterface $jobManager)
    {
        $this->jobManager = $jobManager;
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
        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $this->queueUpdate($node, $language);
    }

    /**
     * @param NodeEvent $event
     */
    public function onUpdateNode(NodeEvent $event)
    {
        $node = $event->getNode();

        // global values (context, restricted) may be changed
        // -> reindex all languages
        foreach ($node->getOnlineLanguage() as $language) {
            $this->queueUpdate($node, $language);
        }
    }

    /**
     * @param MoveNodeEvent $event
     */
    public function onMoveNode(MoveNodeEvent $event)
    {
        $node = $event->getNode();

        foreach ($node->getTree()->findOnlineByTreeNode($node) as $treeOnline) {
            $this->queueUpdate($node, $treeOnline->getLanguage());
        }
    }

    /**
     * @param SetNodeOfflineEvent $event
     */
    public function onSetNodeOffline(SetNodeOfflineEvent $event)
    {
        $language   = $event->getLanguage();
        $node       = $event->getNode();

        $this->queueRemove($node, $language);
    }

    /**
     * @param NodeEvent $event
     */
    public function onDeleteNode(NodeEvent $event)
    {
        $node = $event->getNode();

        $this->queueRemove($node);
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     */
    private function queueRemove(TreeNodeInterface $node, $language = null)
    {

    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     */
    private function queueUpdate(TreeNodeInterface $node, $language)
    {
        $identifier = 'element_' . $node->getId() . '_' . $language;

        $job = new Job('indexer-element', array('--documentId', $identifier));
        $this->jobManager->addUniqueJob($job);
    }
}
