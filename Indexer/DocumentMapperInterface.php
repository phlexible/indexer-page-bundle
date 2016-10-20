<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;

/**
 * Element document mapper interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface DocumentMapperInterface
{
    /**
     * Map node to document.
     *
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     *
     * @return bool
     */
    public function mapDocument(DocumentInterface $document, DocumentDescriptor $descriptor);
}
