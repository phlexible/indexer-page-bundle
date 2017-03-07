<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Event;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Symfony\Component\EventDispatcher\Event;

/**
 * Elements indexer event.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MapDocumentEvent extends Event
{
    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @var PageDocumentDescriptor
     */
    private $descriptor;

    /**
     * @param DocumentInterface  $document
     * @param PageDocumentDescriptor $descriptor
     */
    public function __construct(DocumentInterface $document, PageDocumentDescriptor $descriptor)
    {
        $this->document = $document;
        $this->descriptor = $descriptor;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return PageDocumentDescriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }
}
