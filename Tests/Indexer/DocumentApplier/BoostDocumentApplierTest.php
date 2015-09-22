<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier\BoostDocumentApplier;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;

/**
 * Boost document applier test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class BoostDocumentApplierTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyBoost()
    {
        $document = new ElementDocument();
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
