<?php

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ElementIndexer;

class ElementIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElementIndexer
     */
    private $indexer;

    public function setUp()
    {
        $this->storage = $this->prophesize('Phlexible\Bundle\IndexerBundle\Storage\StorageInterface');
        $this->mapper = $this->prophesize('Phlexible\Bundle\IndexerElementBundle\Indexer\ElementDocumentMapper');
        $this->jobManager = $this->prophesize('Phlexible\Bundle\QueueBundle\Model\JobManagerInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');

        $this->indexer = new ElementIndexer(
            $this->storage->reveal(),
            $this->mapper->reveal(),
            $this->jobManager->reveal(),
            $this->logger->reveal()
        );
    }

    public function testSupportedIdentifier()
    {
        $this->assertTrue($this->indexer->supports('test'));
    }

    public function testUnsupportedIdentifier()
    {
        $this->assertFalse($this->indexer->supports('test'));
    }
}
