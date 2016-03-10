<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;

/**
 * Element document mapper interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface DocumentMapperInterface
{
    /**
     * Map node to document
     *
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     *
     * @return DocumentInterface
     * @throws \Exception
     */
    public function mapDocument(DocumentInterface $document, DocumentDescriptor $descriptor);
}
