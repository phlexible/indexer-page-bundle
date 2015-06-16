<?php

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerBundle\Storage\Operation\Operations;
use Phlexible\Bundle\IndexerBundle\Storage\StorageInterface;
use Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementDocumentMapper;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementIndexer;
use Phlexible\Bundle\QueueBundle\Model\JobManagerInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class ElementIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElementIndexer
     */
    private $indexer;

    /**
     * @var StorageInterface|ObjectProphecy
     */
    private $storage;

    /**
     * @var ElementDocumentMapper|ObjectProphecy
     */
    private $mapper;

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
        $this->storage = $this->prophesize('Phlexible\Bundle\IndexerBundle\Storage\StorageInterface');
        $this->mapper = $this->prophesize('Phlexible\Bundle\IndexerElementBundle\Indexer\ElementDocumentMapper');
        $this->jobManager = $this->prophesize('Phlexible\Bundle\QueueBundle\Model\JobManagerInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');

        $this->storage->createOperations()->willReturn(new Operations());
        $this->mapper->mapIdentifier('testIdentifier')->willReturn(new ElementDocument());

        $this->indexer = new ElementIndexer(
            $this->storage->reveal(),
            $this->mapper->reveal(),
            $this->jobManager->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupportedIdentifier()
    {
        $this->mapper->matchIdentifier('element_74_de')->willReturn(true);
        $this->assertTrue($this->indexer->supports('element_74_de'));
    }

    public function testUnsupportedIdentifier()
    {
        $this->mapper->matchIdentifier('test')->willReturn(false);
        $this->assertFalse($this->indexer->supports('test'));
    }

    public function testAdd()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add('testIdentifier');
    }

    public function testAddWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->add('testIdentifier', true);
    }

    public function testAddWithoutDocument()
    {
        $this->mapper->mapIdentifier('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->add('testIdentifier');
    }

    public function testUpdate()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update('testIdentifier');
    }

    public function testUpdateWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->update('testIdentifier', true);
    }

    public function testUpdateWithoutDocument()
    {
        $this->mapper->mapIdentifier('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->update('testIdentifier');
    }

    public function testDelete()
    {
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete('testIdentifier');
    }

    public function testDeleteWithQueue()
    {
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->delete('testIdentifier', true);
    }

    public function testDeleteWithoutDocument()
    {
        $this->mapper->mapIdentifier('testIdentifier')->willReturn(null);
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->delete('testIdentifier');
    }

    public function testIndexAll()
    {
        $this->mapper->findIdentifiers()->willReturn(array('treenode_1_de', 'treenode_2_en'));
        $this->mapper->mapIdentifier('treenode_1_de')->willReturn(new ElementDocument());
        $this->mapper->mapIdentifier('treenode_2_en')->willReturn(new ElementDocument());
        $this->storage->execute(Argument::cetera())->shouldBeCalled();
        $this->storage->queue(Argument::cetera())->shouldNotBeCalled();

        $this->indexer->indexAll();
    }

    public function testIndexAllWithQueue()
    {
        $this->mapper->findIdentifiers()->willReturn(array('treenode_1_de', 'treenode_2_en'));
        $this->mapper->mapIdentifier('treenode_1_de')->willReturn(new ElementDocument());
        $this->mapper->mapIdentifier('treenode_2_en')->willReturn(new ElementDocument());
        $this->storage->execute(Argument::cetera())->shouldNotBeCalled();
        $this->storage->queue(Argument::cetera())->shouldBeCalled();

        $this->indexer->indexAll(true);
    }
}
