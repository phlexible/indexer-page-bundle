<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Element indexer
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementIndexer implements IndexerInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var ElementDocumentMapper
     */
    private $mapper;

    /**
     * @var JobManagerInterface
     */
    private $jobManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StorageInterface      $storage
     * @param ElementDocumentMapper $mapper
     * @param JobManagerInterface   $jobManager
     * @param LoggerInterface       $logger
     */
    public function __construct(
        StorageInterface $storage,
        ElementDocumentMapper $mapper,
        JobManagerInterface $jobManager,
        LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->mapper = $mapper;
        $this->jobManager = $jobManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Elements indexer';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'element';
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
    public function getDocumentClass()
    {
        return $this->mapper->getDocumentClass();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($identifier)
    {
        return $identifier instanceof ElementDocument || $this->mapper->matchIdentifier($identifier);
    }

    /**
     * @param string $method
     * @param string $identifier
     */
    private function queueOperation($method, $identifier)
    {
        $method .= 'Identifier';

        $operations = $this->storage->createOperations()
            ->$method($identifier)
            ->commit();

        $this->storage->queue($operations);
    }

    /**
     * @param string            $method
     * @param DocumentInterface $document
     */
    private function executeOperation($method, DocumentInterface $document)
    {
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
        if ($viaQueue) {
            $identifier = $this->mapper->createIdentifier($node, $language);
            $this->queueOperation('add', $identifier);
        } else {
            $document = $this->mapper->mapNode($node, $language);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('add', $document);
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
        if ($viaQueue) {
            $identifier = $this->mapper->createIdentifier($node, $language);
            $this->queueOperation('update', $identifier);
        } else {
            $document = $this->mapper->mapNode($node, $language);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('update', $document);
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
        if ($viaQueue) {
            $identifier = $this->mapper->createIdentifier($node, $language);
            $this->queueOperation('delete', $identifier);
        } else {
            $document = $this->mapper->mapNode($node, $language);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('delete', $document);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function add($identifier, $viaQueue = false)
    {
        if ($viaQueue) {
            $this->queueOperation('add', $identifier);
        } else {
            $document = $this->mapper->mapIdentifier($identifier);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('add', $document);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function update($identifier, $viaQueue = false)
    {
        if ($viaQueue) {
            $this->queueOperation('update', $identifier);
        } else {
            $document = $this->mapper->mapIdentifier($identifier);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('update', $document);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($identifier, $viaQueue = false)
    {
        if ($viaQueue) {
            $this->queueOperation('delete', $identifier);
        } else {
            $document = $this->mapper->mapIdentifier($identifier);
            if (!$document) {
                return 0;
            }
            $this->executeOperation('delete', $document);
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll($viaQueue = false)
    {
        $identifiers = $this->mapper->findIdentifiers();

        $operations = $this->storage->createOperations();

        $cnt = 0;
        foreach ($identifiers as $identifier) {
            if ($viaQueue) {
                $operations->addIdentifier($identifier);
            } else {
                $document = $this->mapper->mapIdentifier($identifier);
                if (!$document) {
                    continue;
                }
                $operations->addDocument($document);
            }

            $cnt++;
        }

        $operations->commit();

        if (!$viaQueue) {
            $this->storage->execute($operations);
        } else {
            $this->storage->queue($operations);
        }

        return $cnt;
    }
}
