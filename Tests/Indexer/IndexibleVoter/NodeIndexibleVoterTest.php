<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter\NodeIndexibleVoter;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeInterface;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Phlexible\Bundle\TreeBundle\ContentTree\DelegatingContentTree;
use Phlexible\Bundle\TreeBundle\Model\TreeInterface;
use Psr\Log\LoggerInterface;

/**
 * Node indexible voter
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class NodeIndexibleVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeIndexibleVoter
     */
    private $voter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->voter = new NodeIndexibleVoter($this->logger->reveal());
    }

    public function testVoteReturnsDenyOnUnpublishedNode()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getField($node, 'forward', null)->willReturn(null);
        $tree->getPublishedVersion($node, 'de')->willReturn(null);
        $node->setTree($tree->reveal());
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->logger->info('TreeNode 123 not indexed, node not published')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(NodeIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteReturnsDenyOnDisabledIndex()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setAttribute('searchNoIndex', true);
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getField($node, 'forward', null)->willReturn(null);
        $tree->getPublishedVersion($node, 'de')->willReturn(50);
        $node->setTree($tree->reveal());
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->logger->info('TreeNode 123 not indexed, node is marked with no-index')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(NodeIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteReturnsDenyOnSkilledNodeId()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getField($node, 'forward', null)->willReturn(null);
        $tree->getPublishedVersion($node, 'de')->willReturn(50);
        $node->setTree($tree->reveal());
        $siteroot = new Siteroot();
        $siteroot->setProperty('element_indexer.skip_node_ids', '123,234');
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->logger->info('TreeNode 123 not indexed, node id in skip node list')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(NodeIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteReturnsAllow()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $tree = $this->prophesize(DelegatingContentTree::class);
        $tree->getField($node, 'forward', null)->willReturn(null);
        $tree->getPublishedVersion($node, 'de')->willReturn(50);
        $tree->isInstance($node)->willReturn(false);
        $node->setTree($tree->reveal());
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(NodeIndexibleVoter::VOTE_ALLOW, $result);
    }
}
