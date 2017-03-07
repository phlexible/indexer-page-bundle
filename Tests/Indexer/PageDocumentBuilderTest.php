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
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\PageDocumentMapperInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentBuilder;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Phlexible\Bundle\IndexerPageBundle\Tests\PageDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page document builder test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer
 */
class PageDocumentBuilderTest extends TestCase
{
    use PageDescriptorTrait;

    /**
     * @var PageDocument
     */
    private $document;

    /**
     * @var PageDocumentDescriptor
     */
    private $descriptor;

    /**
     * @var DocumentFactory|ObjectProphecy
     */
    private $documentFactory;

    /**
     * @var PageDocumentMapperInterface|ObjectProphecy
     */
    private $mapper;

    /**
     * @var IndexibleVoterInterface|ObjectProphecy
     */
    private $voter;

    /**
     * @var EventDispatcherInterface|ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var PageDocumentBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->document = new PageDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));

        $this->descriptor = $this->createDescriptor();

        $this->documentFactory = $this->prophesize(DocumentFactory::class);
        $this->documentFactory->factory(PageDocument::class)->willReturn($this->document);
        $this->mapper = $this->prophesize(PageDocumentMapperInterface::class);
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->builder = new PageDocumentBuilder(
            $this->documentFactory->reveal(),
            $this->mapper->reveal(),
            $this->voter->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    public function testBuildReturnsOnDeny()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_DENY);
        $this->mapper->mapDocument($this->document)->shouldNotBeCalled();

        $this->builder->build($this->descriptor);
    }

    public function testBuildCallsMapperOnAllow()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);
        $this->mapper->mapDocument($this->document, $this->descriptor)->shouldBeCalled();

        $this->builder->build($this->descriptor);
    }

    public function testMapDocumentDispatchesMapDocumentEvent()
    {
        $this->voter->isIndexible($this->descriptor)->willReturn(IndexibleVoterInterface::VOTE_ALLOW);

        $this->eventDispatcher->dispatch(IndexerPageEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->builder->build($this->descriptor);
    }
}
