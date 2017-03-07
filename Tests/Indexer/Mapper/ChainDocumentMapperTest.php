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
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\ChainDocumentMapper;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\PageDocumentMapperInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;

/**
 * Chain document applier test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\ChainDocumentApplier
 */
class ChainDocumentApplierTest extends TestCase
{
    public function testMapDocument()
    {
        $document = new PageDocument();
        $identity = new PageDocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $mapper1 = $this->prophesize(PageDocumentMapperInterface::class);
        $mapper2 = $this->prophesize(PageDocumentMapperInterface::class);

        $mapper1->mapDocument($document, $identity)->shouldBeCalled();
        $mapper2->mapDocument($document, $identity)->shouldBeCalled();

        $applier = new ChainDocumentMapper(array($mapper1->reveal(), $mapper2->reveal()));
        $applier->mapDocument($document, $identity);
    }
}
