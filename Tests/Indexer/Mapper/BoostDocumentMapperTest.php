<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\BoostDocumentMapper;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;

/**
 * Boost document mapper test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\BoostDocumentMapper
 */
class BoostDocumentMapperTest extends TestCase
{
    public function testMapDocument()
    {
        $document = new PageDocument();
        $node = new ContentTreeNode();
        $node->setId(123);
        $siteroot = new Siteroot();
        $siteroot->setProperty('page_indexer.boost_node_ids', '123:12');
        $identity = new PageDocumentDescriptor(new DocumentIdentity('abc'), $node, $siteroot, 'de');

        $applier = new BoostDocumentMapper();
        $applier->mapDocument($document, $identity);

        $this->assertSame($document->getBoost(), 12.0);
    }
}
