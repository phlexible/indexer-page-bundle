<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementsComponent\Event;

use Phlexible\Event\Event;
use Phlexible\IndexerComponent\Document\DocumentInterface;
use Phlexible\IndexerElementsComponent\Events;

/**
 * Elements indexer event
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MapDocumentEvent extends Event
{
    /**
     * @var string
     */
    protected $eventName = Events::MAP_DOCUMENT;

    /**
     * @var DocumentInterface
     */
    protected $document = null;

    /**
     * @var Makeweb_Elements_Tree_Node
     */
    protected $node = null;

    /**
     * @var Makeweb_Elements_Element_Version
     */
    protected $elementVersion = null;

    /**
     * @var string
     */
    protected $language = null;

    /**
     * @param DocumentInterface $document
     * @param Makeweb_Elements_Tree_Node         $node
     * @param Makeweb_Elements_Element_Version   $elementVersion
     * @param string                             $language
     */
    public function __construct(DocumentInterface $document,
                                Makeweb_Elements_Tree_Node $node,
                                Makeweb_Elements_Element_Version $elementVersion,
                                $language)
    {
        $this->document       = $document;
        $this->node           = $node;
        $this->elementVersion = $elementVersion;
        $this->language       = $language;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return Makeweb_Elements_Tree_Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return Makeweb_Elements_Element_Version
     */
    public function getElementVersion()
    {
        return $this->elementVersion;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}