<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Tests\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPagerBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentApplier\ChainDocumentApplier;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;

/**
 * Chain document applier test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainDocumentApplierTest extends \PHPUnit_Framework_TestCase
{
    public function testApplierChain()
    {
        $document = new PageDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $applier1 = $this->prophesize(DocumentApplierInterface::class);
        $applier2 = $this->prophesize(DocumentApplierInterface::class);

        $applier1->apply($document, $identity)->shouldBeCalled();
        $applier2->apply($document, $identity)->shouldBeCalled();

        $applier = new ChainDocumentApplier(array($applier1->reveal(), $applier2->reveal()));
        $applier->apply($document, $identity);
    }
}
