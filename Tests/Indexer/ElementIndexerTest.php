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
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentIdentifierInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentMapper;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

/**
 * Element indexer test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentFactory|ObjectProphecy
     */
    private $documentFactory;

    /**
     * @var PageDocument
     */
    private $document;

    /**
     * @var PageIndexer
     */
    private $indexer;

    /**
     * @var StorageInterface|ObjectProphecy
     */
    private $storage;

    /**
     * @var DocumentMapper|ObjectProphecy
     */
    private $mapper;

    /**
     * @var ContentIdentifierInterface|ObjectProphecy
     */
    private $identifier;

    /**
     * @var JobManagerInterface|ObjectProphecy
     */
    private $jobManager;

    /**
     * @var LoggerInterface|ObjectProphecy
     */
    private $logger;

    public function setUp()
    {
        $this->document = new PageDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));
        $this->documentFactory = $this->prophesize(DocumentFactory::class);
        $this->documentFactory->factory(PageDocument::class)->willReturn($this->document);
        $this->storage = $this->prophesize(StorageInterface::class);
        $this->mapper = $this->prophesize(DocumentMapper::class);
        $this->identifier = $this->prophesize(ContentIdentifierInterface::class);
        $this->jobManager = $this->prophesize(JobManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->storage->createOperations()->willReturn(new Operations());

        $this->indexer = new PageIndexer(
            $this->documentFactory->reveal(),
            $this->storage->reveal(),
            $this->mapper->reveal(),
            $this->identifier->reveal(),
            $this->jobManager->reveal(),
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
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled()->willReturn(true);

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
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled()->willReturn(true);

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
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled()->willReturn(true);

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
        $descriptor1 = new DocumentDescriptor(new DocumentIdentity('page_1_de'), new ContentTreeNode(), new Siteroot(), 'de');
        $descriptor2 = new DocumentDescriptor(new DocumentIdentity('page_2_en'), new ContentTreeNode(), new Siteroot(), 'en');
        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));
        $this->mapper->mapDocument(Argument::type(PageDocument::class), $descriptor1)->shouldBeCalled()->willReturn(true);
        $this->mapper->mapDocument(Argument::type(PageDocument::class), $descriptor2)->shouldBeCalled()->willReturn(true);

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testIndexAllWithQueue()
    {
        $descriptor1 = new DocumentDescriptor(new DocumentIdentity('page_1_de'), new ContentTreeNode(), new Siteroot(), 'de');
        $descriptor2 = new DocumentDescriptor(new DocumentIdentity('page_2_en'), new ContentTreeNode(), new Siteroot(), 'en');
        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));
        $this->mapper->mapDocument(Argument::cetera())->shouldNotBeCalled();
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->indexAll(true);
    }
}
