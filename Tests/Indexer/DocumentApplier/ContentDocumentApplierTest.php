<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\DocumentApplier;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\ElementBundle\Entity\Element;
use Phlexible\Bundle\ElementBundle\Entity\ElementVersion;
use Phlexible\Bundle\ElementtypeBundle\Model\Elementtype;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\NoIndexContentFilter;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentRenderer\ContentRendererInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentTitleExtractor\ContentTitleExtractorInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentApplier\ContentDocumentApplier;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Phlexible\Bundle\TreeBundle\ContentTree\DelegatingContentTree;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Content document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentDocumentApplierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentDocumentApplier
     */
    private $applier;

    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var ContentTitleExtractorInterface
     */
    private $titleExtractor;

    /**
     * @var ContentRendererInterface
     */
    private $renderer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->elementService = $this->prophesize(ElementService::class);
        $this->titleExtractor = $this->prophesize(ContentTitleExtractorInterface::class);
        $this->renderer = $this->prophesize(ContentRendererInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->applier = new ContentDocumentApplier(
            $this->elementService->reveal(),
            new NoIndexContentFilter(),
            $this->titleExtractor->reveal(),
            $this->renderer->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testApplyContent()
    {
        $document = new PageDocument();
        $element = new Element();
        $elementVersion = new ElementVersion();
        $elementtype = new Elementtype();
        $elementtype->setId('345');
        $elementtype->setUniqueId('testElementtype');
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getPublishedVersion($node, 'de')->willReturn(50);
        $node->setTree($tree->reveal());
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), $node, new Siteroot(), 'de');

        $this->renderer->render($identity)->willReturn('testContent');
        $this->titleExtractor->extractTitle('testContent')->willReturn('testTitle');
        $this->elementService->findElement(234)->willReturn($element);
        $this->elementService->findElementVersion($element, 50)->willReturn($elementVersion);
        $this->elementService->findElementtype($element)->willReturn($elementtype);

        $this->applier->apply($document, $identity);

        $this->assertSame('testContent', $document->get('content'));
        $this->assertSame('testTitle', $document->get('title'));
        $this->assertSame('345', $document->get('elementtypeId'));
        $this->assertSame('testElementtype', $document->get('elementtype'));
    }
}
