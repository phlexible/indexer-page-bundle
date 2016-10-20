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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentApplier\BoostDocumentApplier;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;

/**
 * Boost document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class BoostDocumentApplierTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyBoost()
    {
        $document = new PageDocument();
        $node = new ContentTreeNode();
        $node->setId(123);
        $siteroot = new Siteroot();
        $siteroot->setProperty('element_indexer.boost_node_ids', '123:12');
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), $node, $siteroot, 'de');

        $applier = new BoostDocumentApplier();
        $applier->apply($document, $identity);

        $this->assertSame($document->getBoost(), 12.0);
    }
}
