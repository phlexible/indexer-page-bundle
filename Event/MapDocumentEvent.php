<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Event;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Symfony\Component\EventDispatcher\Event;

/**
 * Elements indexer event
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
     * @var DocumentDescriptor
     */
    private $descriptor;

    /**
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     */
    public function __construct(DocumentInterface $document, DocumentDescriptor $descriptor)
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
     * @return DocumentDescriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }
}
