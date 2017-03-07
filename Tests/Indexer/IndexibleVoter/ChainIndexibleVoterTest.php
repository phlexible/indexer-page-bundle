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
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\ChainIndexibleVoter;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;

/**
 * Chain indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\ChainIndexibleVoter
 */
class ChainIndexibleVoterTest extends TestCase
{
    public function testIndexibleChainReturnsAllowOnAllAllowed()
    {
        $node = new ContentTreeNode();
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);
        $voter2->isIndexible($descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_ALLOW, $result);
    }

    public function testIndexibleChainReturnsDenyOnSingleDeny()
    {
        $node = new ContentTreeNode();
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($descriptor)->willReturn(ChainIndexibleVoter::VOTE_ALLOW);
        $voter2->isIndexible($descriptor)->willReturn(ChainIndexibleVoter::VOTE_DENY);

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_DENY, $result);
    }

    public function testIndexibleChainReturnsDenyOnFirstDeny()
    {
        $node = new ContentTreeNode();
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $voter1 = $this->prophesize(IndexibleVoterInterface::class);
        $voter2 = $this->prophesize(IndexibleVoterInterface::class);

        $voter1->isIndexible($descriptor)->willReturn(ChainIndexibleVoter::VOTE_DENY);
        $voter2->isIndexible($descriptor)->shouldNotBeCalled();

        $voter = new ChainIndexibleVoter(array($voter1->reveal(), $voter2->reveal()));
        $result = $voter->isIndexible($descriptor);

        $this->assertSame(ChainIndexibleVoter::VOTE_DENY, $result);
    }
}
