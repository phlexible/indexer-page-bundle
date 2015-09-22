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
 * Document applier interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface DocumentApplierInterface
{
    /**
     * @param DocumentInterface $document
     * @param DocumentDescriptor   $descriptor
     */
    public function apply(DocumentInterface $document, DocumentDescriptor $descriptor);
}
