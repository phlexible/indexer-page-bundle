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

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageContentIdentifier;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Phlexible\Bundle\TreeBundle\ContentTree\DelegatingContentTree;
use PHPUnit\Framework\TestCase;

/**
 * Page content identifier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\PageContentIdentifier
 */
class PageContentIdentifierTest extends TestCase
{
    /**
     * @var PageContentIdentifier
     */
    private $identifier;

    /**
     * @var ContentTreeManagerInterface
     */
    private $treeManager;

    /**
     * @var SiterootManagerInterface
     */
    private $siterootManager;

    public function setUp()
    {
        $this->siterootManager = $this->prophesize(SiterootManagerInterface::class);
        $this->treeManager = $this->prophesize(ContentTreeManagerInterface::class);
        $elementService = $this->prophesize(ElementService::class);
        $voter = $this->prophesize(IndexibleVoterInterface::class);

        $this->identifier = new PageContentIdentifier(
            $this->siterootManager->reveal(),
            $this->treeManager->reveal(),
            $elementService->reveal(),
            $voter->reveal()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateValidIdentifierReturnsTrue()
    {
        $this->assertTrue($this->identifier->validateIdentity(new DocumentIdentity('page_123_de')));
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateInvalidIdentifierReturnsFalse()
    {
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('invalid_123_de')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('page_1x23_de')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('page_123_d1e')));
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateIdentityFromNode()
    {
        $siteroot = new Siteroot();
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getSiterootId()->willReturn(123);
        $node = new ContentTreeNode();
        $node->setTree($tree->reveal());
        $this->siterootManager->find(123)->willReturn($siteroot);

        $descriptor = $this->identifier->createDescriptorFromNode($node, 'de');

        $this->assertInstanceOf(PageDocumentDescriptor::class, $descriptor);
        $this->assertSame($node, $descriptor->getNode());
        $this->assertSame($siteroot, $descriptor->getSiteroot());
        $this->assertSame('de', $descriptor->getLanguage());
    }

    /**
     * {@inheritdoc}
     */
    public function testCreateIdentityFromIdentifier()
    {
        $siteroot = new Siteroot();
        $node = new ContentTreeNode();
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->get(123)->willReturn($node);
        $tree->getSiterootId()->willReturn(123);
        $this->treeManager->findByTreeId(123)->willReturn($tree->reveal());
        $this->siterootManager->find(123)->willReturn($siteroot);

        $identity = new DocumentIdentity('page_123_de');
        $descriptor = $this->identifier->createDescriptorFromIdentity($identity);

        $this->assertInstanceOf(PageDocumentDescriptor::class, $descriptor);
        $this->assertSame($identity, $descriptor->getIdentity());
        $this->assertSame($node, $descriptor->getNode());
        $this->assertSame($siteroot, $descriptor->getSiteroot());
        $this->assertSame('de', $descriptor->getLanguage());
    }
}
