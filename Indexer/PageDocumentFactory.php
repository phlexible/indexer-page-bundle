<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;

/**
 * Page document factory.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PageDocumentFactory
{
    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @param DocumentFactory $documentFactory
     * @param string          $documentClass
     */
    public function __construct(
        DocumentFactory $documentFactory,
        $documentClass = PageDocument::class
    ) {
        $this->documentFactory = $documentFactory;
        $this->documentClass = $documentClass;
    }

    /**
     * @return PageDocument
     */
    public function createDocument()
    {
        return $this->documentFactory->factory($this->documentClass);
    }
}
