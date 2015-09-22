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
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier\ChainDocumentApplier;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier\DocumentApplierInterface;
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
        $document = new ElementDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $applier1 = $this->prophesize(DocumentApplierInterface::class);
        $applier2 = $this->prophesize(DocumentApplierInterface::class);

        $applier1->apply($document, $identity)->shouldBeCalled();
        $applier2->apply($document, $identity)->shouldBeCalled();

        $applier = new ChainDocumentApplier(array($applier1->reveal(), $applier2->reveal()));
        $applier->apply($document, $identity);
    }
}
