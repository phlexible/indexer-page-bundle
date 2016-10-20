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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPagerBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPagerBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPagerBundle\IndexerElementEvents;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Document mapper test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DocumentMapperTest extends \PHPUnit_Framework_TestCase
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
     * @var DocumentMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->applier = $this->prophesize(DocumentApplierInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->mapper = new DocumentMapper(
            $this->voter->reveal(),
            $this->applier->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testMapIdentityReturnsNullOnDeniedVoter()
    {
        $document = new PageDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->voter->isIndexible($identity)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $this->mapper->mapDocument($document, $identity);
    }

    public function testMapIdentityReturnsDocument()
    {
        $document = new PageDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->mapper->mapDocument($document, $identity);

        $this->assertInstanceOf(PageDocument::class, $document);
    }

    public function testMapIdentityDispatchesMapDocumentEvent()
    {
        $document = new PageDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->dispatcher->dispatch(IndexerElementEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->mapper->mapDocument($document, $identity);
    }
}
