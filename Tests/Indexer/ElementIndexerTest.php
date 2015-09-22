<?php

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerBundle\Storage\Operation\Operations;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentIdentifierInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentMapper;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementIndexer;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ElementIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentFactory|ObjectProphecy
     */
    private $documentFactory;

    /**
     * @var ElementDocument
     */
    private $document;

    /**
     * @var ElementIndexer
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
        $this->document = new ElementDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));
        $this->documentFactory = $this->prophesize(DocumentFactory::class);
        $this->documentFactory->factory('Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument')->willReturn($this->document);
        $this->storage = $this->prophesize(StorageInterface::class);
        $this->mapper = $this->prophesize(DocumentMapper::class);
        $this->identifier = $this->prophesize(ContentIdentifierInterface::class);
        $this->jobManager = $this->prophesize(JobManagerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->storage->createOperations()->willReturn(new Operations());

        $this->indexer = new ElementIndexer(
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
        $identity = new DocumentIdentity('element_74_de');

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
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled();

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testAddWithQueue()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->add($identity, true);
    }

    public function testAddWithoutDocument()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add($identity);
    }

    public function testUpdate()
    {
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled();

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testUpdateWithQueue()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->update($identity, true);
    }

    public function testUpdateWithoutDocument()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update($identity);
    }

    public function testDelete()
    {
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, new ContentTreeNode(), new Siteroot(), 'de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn($descriptor);
        $this->mapper->mapDocument($this->document, $descriptor)->shouldBeCalled();

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testDeleteWithQueue()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete($identity, true);
    }

    public function testDeleteWithoutDocument()
    {
        $identity = new DocumentIdentity('element_74_de');

        $this->identifier->createDescriptorFromIdentity($identity)->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete($identity);
    }

    public function testIndexAll()
    {
        $descriptor1 = new DocumentDescriptor(new DocumentIdentity('treenode_1_de'), new ContentTreeNode(), new Siteroot(), 'de');
        $descriptor2 = new DocumentDescriptor(new DocumentIdentity('treenode_2_en'), new ContentTreeNode(), new Siteroot(), 'en');
        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));
        $this->mapper->mapDocument(Argument::type(ElementDocument::class), $descriptor1)->shouldBeCalled();
        $this->mapper->mapDocument(Argument::type(ElementDocument::class), $descriptor2)->shouldBeCalled();

        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testIndexAllWithQueue()
    {
        $descriptor1 = new DocumentDescriptor(new DocumentIdentity('treenode_1_de'), new ContentTreeNode(), new Siteroot(), 'de');
        $descriptor2 = new DocumentDescriptor(new DocumentIdentity('treenode_2_en'), new ContentTreeNode(), new Siteroot(), 'en');
        $this->identifier->findAllDescriptors()->willReturn(array($descriptor1, $descriptor2));
        $this->mapper->mapDocument(Argument::cetera())->shouldNotBeCalled();
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->indexAll(true);
    }
}
