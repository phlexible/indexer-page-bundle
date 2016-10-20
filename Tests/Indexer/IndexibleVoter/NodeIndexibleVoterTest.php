<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\NodeIndexibleVoter;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Phlexible\Bundle\TreeBundle\ContentTree\DelegatingContentTree;
use Psr\Log\LoggerInterface;

/**
 * Node indexible voter.
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
        $identity = new DocumentIdentity('page_74_de');
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
        $identity = new DocumentIdentity('page_74_de');
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
        $siteroot->setProperty('page_indexer.skip_node_ids', '123,234');
        $identity = new DocumentIdentity('page_74_de');
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
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(NodeIndexibleVoter::VOTE_ALLOW, $result);
    }
}
