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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentMapper;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentMapper
 */
class DocumentMapperTest extends TestCase
{
    /**
     * @var IndexibleVoterInterface
     */
    private $voter;

    /**
     * @var DocumentApplierInterface
     */
    private $applier;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PageDocument
     */
    private $document;

    /**
     * @var DocumentDescriptor
     */
    private $descriptor;

    /**
     * @var DocumentMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->applier = $this->prophesize(DocumentApplierInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->document = new PageDocument();
        $this->descriptor = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->mapper = new DocumentMapper(
            $this->voter->reveal(),
            $this->applier->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testMapDocumentReturnsFalseOnNotIndexible()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $result = $this->mapper->mapDocument($this->document, $this->descriptor);

        $this->assertFalse($result);
    }

    public function testMapDocumentCallsApplier()
    {
        $this->applier->apply($this->document, $this->descriptor)->shouldBeCalled();

        $this->mapper->mapDocument($this->document, $this->descriptor);
    }

    public function testMapDocumentDispatchesMapDocumentEvent()
    {
        $this->dispatcher->dispatch(IndexerPageEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->mapper->mapDocument($this->document, $this->descriptor);
    }

    public function testMapDocumentReturnsTrueOnSuccess()
    {
        $result = $this->mapper->mapDocument($this->document, $this->descriptor);

        $this->assertTrue($result);
    }
}
