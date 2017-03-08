<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page indexer.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PageIndexer implements PageIndexerInterface
{
    /**
     * @var PageDocumentBuilder
     */
    private $builder;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var PageContentIdentifierInterface
     */
    private $identifier;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param PageDocumentBuilder            $builder
     * @param StorageInterface               $storage
     * @param PageContentIdentifierInterface $identifier
     * @param JobManagerInterface            $jobManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param LoggerInterface                $logger
     * @param int                            $batchSize
     */
    public function __construct(
        PageDocumentBuilder $builder,
        StorageInterface $storage,
        PageContentIdentifierInterface $identifier,
        JobManagerInterface $jobManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        $batchSize = 50
    ) {
        $this->builder = $builder;
        $this->storage = $storage;
        $this->identifier = $identifier;
        $this->jobManager = $jobManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'page';
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
    public function createDocument()
    {
        return $this->builder->createDocument();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(DocumentIdentity $identity)
    {
        return $this->identifier->validateIdentity($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function find(DocumentIdentity $identity)
    {
        return $this->storage->find($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->storage->countType($this->createDocument()->getName());
    }
    /**
     * @param string           $method
     * @param DocumentIdentity $identity
     */
    private function queueIdentityOperation($method, DocumentIdentity $identity)
    {
        $method .= 'Identity';

        $operations = $this->storage->createOperations()
            ->$method($identity)
            ->commit();

        $this->storage->queue($operations);
    }

    /**
     * @param string             $method
     * @param PageDocumentDescriptor $descriptor
     */
    private function queueDescriptorOperation($method, PageDocumentDescriptor $descriptor)
    {
        $method .= 'Identity';

        $operations = $this->storage->createOperations()
            ->$method($descriptor->getIdentity())
            ->commit();

        $this->storage->queue($operations);
    }

    /**
     * @param string           $method
     * @param DocumentIdentity $identity
     */
    private function executeIdentityOperation($method, DocumentIdentity $identity)
    {
        $descriptor = $this->identifier->createDescriptorFromIdentity($identity);
        if (!$descriptor) {
            return;
        }

        if (!($document = $this->builder->build($descriptor))) {
            return;
        }

        $method .= 'Document';

        $operations = $this->storage->createOperations()
            ->$method($document)
            ->commit();

        $this->storage->execute($operations);
    }

    /**
     * @param string             $method
     * @param PageDocumentDescriptor $descriptor
     */
    private function executeDescriptorOperation($method, PageDocumentDescriptor $descriptor)
    {
        if (!($document = $this->builder->build($descriptor))) {
            return;
        }

        $method .= 'Document';

        $operations = $this->storage->createOperations()
            ->$method($document)
            ->commit();

        $this->storage->execute($operations);
    }


    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function addNode(TreeNodeInterface $node, $language, $viaQueue = false)
    {
        $this->logger->debug("addNode {$node->getId()} {$language}");

        $descriptor = $this->identifier->createDescriptorFromNode($node, $language);

        if ($viaQueue) {
            $this->queueDescriptorOperation('add', $descriptor);
        } else {
            $this->executeDescriptorOperation('add', $descriptor);
        }

        return 1;
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function updateNode(TreeNodeInterface $node, $language, $viaQueue = false)
    {
        $this->logger->debug("updateNode {$node->getId()} {$language}");

        $descriptor = $this->identifier->createDescriptorFromNode($node, $language);

        if ($viaQueue) {
            $this->queueDescriptorOperation('update', $descriptor);
        } else {
            $this->executeDescriptorOperation('update', $descriptor);
        }

        return 1;
    }

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function deleteNode(TreeNodeInterface $node, $language, $viaQueue = false)
    {
        $this->logger->debug("deleteNode {$node->getId()} {$language}");

        $descriptor = $this->identifier->createDescriptorFromNode($node, $language);

        if ($viaQueue) {
            $this->queueDescriptorOperation('delete', $descriptor);
        } else {
            $this->executeDescriptorOperation('delete', $descriptor);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function add(DocumentIdentity $identity, $viaQueue = false)
    {
        $this->logger->debug("add {$identity}");

        if ($viaQueue) {
            $this->queueIdentityOperation('add', $identity);
        } else {
            $this->executeIdentityOperation('add', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function update(DocumentIdentity $identity, $viaQueue = false)
    {
        $this->logger->debug("update {$identity}");

        if ($viaQueue) {
            $this->queueIdentityOperation('update', $identity);
        } else {
            $this->executeIdentityOperation('update', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(DocumentIdentity $identity, $viaQueue = false)
    {
        if ($viaQueue) {
            $this->queueIdentityOperation('delete', $identity);
        } else {
            $this->executeIdentityOperation('delete', $identity);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll()
    {
        $descriptors = $this->identifier->findAllDescriptors();
        $operations = $this->storage->createOperations();

        $handled = 0;
        $batch = 0;

        $this->eventDispatcher->dispatch(IndexerPageEvents::INDEX_ALL_DOCUMENTS);

        foreach ($descriptors as $descriptor) {
            ++$handled;

            $this->logger->info("indexAll add {$descriptor->getNode()->getId()} {$descriptor->getLanguage()}");

            if (!($document = $this->builder->build($descriptor))) {
                $this->logger->warning("indexAll skipping {$descriptor->getNode()->getId()} {$descriptor->getLanguage()}");
                continue;
            }
            $operations->addDocument($document);

            ++$batch;

            if ($batch % $this->batchSize === 0) {
                $this->logger->notice("indexAll batch commit ($handled)");

                $operations->commit();

                $this->storage->execute($operations);

                $operations = $this->storage->createOperations();
            }
        }

        if (count($operations)) {
            $this->logger->notice("indexAll commit ($handled)");

            $operations->commit();

            $this->storage->execute($operations);
        }

        return $handled;
    }

    /**
     * {@inheritdoc}
     */
    public function queueAll()
    {
        $descriptors = $this->identifier->findAllDescriptors();
        $operations = $this->storage->createOperations();

        $handled = 0;
        $batch = 0;
        $total = count($descriptors);

        $this->eventDispatcher->dispatch(IndexerPageEvents::QUEUE_ALL_DOCUMENTS);

        foreach ($descriptors as $descriptor) {
            ++$handled;

            $this->logger->info("queueAll add {$descriptor->getNode()->getId()} {$descriptor->getLanguage()}");

            $operations->addIdentity($descriptor->getIdentity());

            ++$batch;

            if ($batch % $this->batchSize === 0) {
                $this->logger->notice("queueAll batch commit ($handled/$total)");

                $operations->commit();

                $this->storage->queue($operations);

                $operations = $this->storage->createOperations();
            }
        }

        if (count($operations)) {
            $this->logger->notice("queueAll commit ($handled/$total)");

            $operations->commit();

            $this->storage->queue($operations);
        }

        return $handled;
    }
}
