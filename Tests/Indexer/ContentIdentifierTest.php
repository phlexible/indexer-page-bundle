<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentIdentifier;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\SiterootBundle\Model\SiterootManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeManagerInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Phlexible\Bundle\TreeBundle\ContentTree\DelegatingContentTree;

/**
 * Content identifier test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentIdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentIdentifier
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

        $this->identifier = new ContentIdentifier(
            $this->siterootManager->reveal(),
            $this->treeManager->reveal(),
            $elementService->reveal()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateValidIdentifierReturnsTrue()
    {
        $this->assertTrue($this->identifier->validateIdentity(new DocumentIdentity('element_123_de')));
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateInvalidIdentifierReturnsFalse()
    {
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('xelement_123_de')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('element_1x23_de')));
        $this->assertFalse($this->identifier->validateIdentity(new DocumentIdentity('element_123_d1e')));
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

        $this->assertInstanceOf(DocumentDescriptor::class, $descriptor);
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

        $identity = new DocumentIdentity('element_123_de');
        $descriptor = $this->identifier->createDescriptorFromIdentity($identity);

        $this->assertInstanceOf(DocumentDescriptor::class, $descriptor);
        $this->assertSame($identity, $descriptor->getIdentity());
        $this->assertSame($node, $descriptor->getNode());
        $this->assertSame($siteroot, $descriptor->getSiteroot());
        $this->assertSame('de', $descriptor->getLanguage());
    }
}
