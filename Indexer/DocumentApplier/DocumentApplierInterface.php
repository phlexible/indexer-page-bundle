<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;

/**
 * Document applier interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface DocumentApplierInterface
{
    /**
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     */
    public function apply(DocumentInterface $document, DocumentDescriptor $descriptor);
}
