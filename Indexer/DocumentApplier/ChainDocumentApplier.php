<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;

/**
 * Chain document applier
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var DocumentApplierInterface[]
     */
    private $appliers;

    /**
     * @param DocumentApplierInterface[] $appliers
     */
    public function __construct(array $appliers)
    {
        $this->appliers = $appliers;
    }

    /**
     * @param DocumentInterface $document
     * @param DocumentDescriptor   $descriptor
     */
    public function apply(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        foreach ($this->appliers as $applier) {
            $applier->apply($document, $descriptor);
        }
    }
}
