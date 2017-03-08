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
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentFactory;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Phlexible\Bundle\IndexerPageBundle\Tests\PageDescriptorTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Page document factory test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\PageIndexer
 */
class PageDocumentFactoryTest extends TestCase
{
    /**
     * @var PageDocument
     */
    private $document;

    /**
     * @var DocumentFactory|ObjectProphecy
     */
    private $documentFactory;

    public function setUp()
    {
        $this->document = new PageDocument();
        $this->document->setIdentity(new DocumentIdentity('A'));

        $this->documentFactory = $this->prophesize(DocumentFactory::class);
    }

    public function testCreateDocument()
    {
        $this->documentFactory->factory(PageDocument::class)->willReturn($this->document);

        $factory = new PageDocumentFactory($this->documentFactory->reveal());
        $result = $factory->createDocument();

        $this->assertSame($this->document, $result);
    }

    public function testCreateDocumentWithDifferentClass()
    {
        $this->documentFactory->factory(\stdClass::class)->willReturn($this->document);

        $factory = new PageDocumentFactory($this->documentFactory->reveal(), \stdClass::class);
        $result = $factory->createDocument();

        $this->assertSame($this->document, $result);
    }
}
