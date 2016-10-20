<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\IndexibleVoter\SiterootIndexibleVoter;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Psr\Log\LoggerInterface;

/**
 * Node indexible voter
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class SiterootIndexibleVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SiterootIndexibleVoter
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

        $this->voter = new SiterootIndexibleVoter($this->logger->reveal());
    }

    public function testVoteReturnsAllowOnEmptyDisableProperty()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(SiterootIndexibleVoter::VOTE_ALLOW, $result);
    }

    public function testVoteReturnsAllowOnFalseDisableProperty()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $siteroot = new Siteroot();
        $siteroot->setProperty('element_indexer.disabled', false);
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(SiterootIndexibleVoter::VOTE_ALLOW, $result);
    }

    public function testVoteReturnsDenyOnTrueDisableProperty()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $siteroot = new Siteroot();
        $siteroot->setProperty('element_indexer.disabled', true);
        $identity = new DocumentIdentity('element_74_de');
        $descriptor = new DocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->logger->info('TreeNode 123 not indexed, siteroot is disabled')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(SiterootIndexibleVoter::VOTE_DENY, $result);
    }
}
