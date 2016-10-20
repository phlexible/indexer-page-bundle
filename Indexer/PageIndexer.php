<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerPagerBundle\Document\PageDocument;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Element indexer
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PageIndexer implements IndexerInterface
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var DocumentMapperInterface
     */
    private $mapper;

    /**
     * @var ContentIdentifierInterface
     */
    private $identifier;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @param DocumentFactory            $documentFactory
     * @param StorageInterface           $storage
     * @param DocumentMapperInterface    $mapper
     * @param ContentIdentifierInterface $identifier
     * @param JobManagerInterface        $jobManager
     * @param LoggerInterface            $logger
     * @param string                     $documentClass
     */
    public function __construct(
        DocumentFactory $documentFactory,
        StorageInterface $storage,
        DocumentMapperInterface $mapper,
        ContentIdentifierInterface $identifier,
        JobManagerInterface $jobManager,
        LoggerInterface $logger,
        $documentClass = PageDocument::class
    ) {
        $this->documentFactory = $documentFactory;
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->identifier = $identifier;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
        $this->documentClass = $documentClass;
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
    public function supports(DocumentIdentity $identity)
    {
        return $this->identifier->validateIdentity($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function createDocument()
    {
        return $this->documentFactory->factory($this->documentClass);
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
     * @param DocumentDescriptor $descriptor
     */
    private function queueDescriptorOperation($method, DocumentDescriptor $descriptor)
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

        $document = $this->createDocument();
        if (!$this->mapper->mapDocument($document, $descriptor)) {
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
     * @param DocumentDescriptor $descriptor
     */
    private function executeDescriptorOperation($method, DocumentDescriptor $descriptor)
    {
        $document = $this->createDocument();
        if (!$this->mapper->mapDocument($document, $descriptor)) {
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
    public function indexAll($viaQueue = false)
    {
        //$descriptors = $this->identifier->findAllDescriptors();

        $operations = $this->storage->createOperations();

        foreach ($this->identifier->findAllDescriptors() as $descriptor) {
            $this->logger->info("indexAll {$descriptor->getNode()->getId()} {$descriptor->getLanguage()}");

            if ($viaQueue) {
                $operations->addIdentity($descriptor->getIdentity());
            } else {
                $document = $this->createDocument();
                if ($this->mapper->mapDocument($document, $descriptor)) {
                    $operations->addDocument($document);
                }
            }
        }

        if (!count($operations)) {
            return 0;
        }

        $operations->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return count($operations);
    }
}
