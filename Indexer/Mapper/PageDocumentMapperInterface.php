<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;

/**
 * Page document mapper interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface PageDocumentMapperInterface
{
    /**
     * Map node to document.
     *
     * @param DocumentInterface      $document
     * @param PageDocumentDescriptor $descriptor
     */
    public function mapDocument(DocumentInterface $document, PageDocumentDescriptor $descriptor);
}
