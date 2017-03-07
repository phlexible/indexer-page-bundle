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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\BaseDocumentMapper;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;

/**
 * Base document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\BaseDocumentMapper
 */
class BaseDocumentMapperTest extends TestCase
{
    public function testMapDocument()
    {
        $document = new PageDocument();
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $node->setInNavigation(true);
        $siteroot = new Siteroot(345);
        $identity = new PageDocumentDescriptor(new DocumentIdentity('abc'), $node, $siteroot, 'de');

        $applier = new BaseDocumentMapper();
        $applier->mapDocument($document, $identity);

        $this->assertSame($document->get('language'), 'de');
        $this->assertSame($document->get('nodeId'), 123);
        $this->assertSame($document->get('typeId'), 234);
        $this->assertSame($document->get('siterootId'), 345);
        $this->assertSame($document->get('navigation'), true);
    }
}
