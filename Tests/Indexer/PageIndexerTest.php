<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Storage\Operation\Operations;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\PageDocumentMapperInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageContentIdentifierInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentBuilder;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer;
use Phlexible\Bundle\IndexerPageBundle\Tests\PageDescriptorTrait;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page indexer test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer
 */
class PageIndexerTest extends TestCase
{
    use PageDescriptorTrait;

    /**
     * @var PageDocument
     */
    private $document;

    /**
     * @var PageDocumentBuilder|ObjectProphecy
     */
    private $builder;

    /**
     * @var StorageInterface|ObjectProphecy
     */
    private $storage;

    /**
     * @var PageDocumentMapperInterface|ObjectProphecy
     */
    private $mapper;

    /**
     * @var PageContentIdentifierInterface|ObjectProphecy
     */
    private $identifier;

    /**
     * @var JobManagerInterface|ObjectProphecy
     */
    private $jobManager;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    /**
     * @var PageIndexer
     */
    private $indexer;

    public function setUp()
    {
        $this->document = new PageDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));

        $this->builder = $this->prophesize(PageDocumentBuilder::class);
        $this->storage = $this->prophesize(StorageInterface::class);
        $this->mapper = $this->prophesize(PageDocumentMapperInterface::class);
        $this->identifier = $this->prophesize(PageContentIdentifierInterface::class);
        $this->jobManager = $this->prophesize(JobManagerInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->storage->createOperations()->willReturn(new Operations());

        $this->indexer = new PageIndexer(
            $this->builder->reveal(),
            $this->storage->reveal(),
            $this->identifier->reveal(),
            $this->jobManager->reveal(),
            $this->eventDispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupportedIdentifier()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->identifier->validateIdentity($identity)->willReturn(true);

        $this->assertTrue($this->indexer->supports($identity));
    }

    public function testUnsupportedIdentifier()
    {
        $identity = new DocumentIdentity('invalid');

        $this->identifier->validateIdentity($identity)->willReturn(false);

        $this->assertFalse($this->indexer->supports($identity));
    }

    public function testAdd()
    {
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testAddWithQueue()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->add($identity, true);
    }

    public function testAddWithoutDocument()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testUpdate()
    {
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testUpdateWithQueue()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->update($identity, true);
    }

    public function testUpdateWithoutDocument()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testDelete()
    {
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = $this->createDescriptor();

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->builder->build($descriptor)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testDeleteWithQueue()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete($identity, true);
    }

    public function testDeleteWithoutDocument()
    {
        $identity = new DocumentIdentity('page_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testIndexAll()
    {
        $descriptor1 = $this->createDescriptor();
        $descriptor2 = $this->createDescriptor();

        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));

        $this->builder->build($descriptor1)->shouldBeCalled()->willReturn($this->document);
        $this->builder->build($descriptor2)->shouldBeCalled()->willReturn($this->document);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testQueueAll()
    {
        $descriptor1 = $this->createDescriptor();
        $descriptor2 = $this->createDescriptor();

        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));

        $this->builder->build($descriptor1)->shouldNotBeCalled();
        $this->builder->build($descriptor2)->shouldNotBeCalled();

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->queueAll();
    }
}
