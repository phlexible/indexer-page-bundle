<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementsComponent\Event;

use Phlexible\ElementsComponent\ElementVersion\ElementVersion;
use Phlexible\IndexerComponent\Document\DocumentInterface;
use Phlexible\TreeComponent\Tree\Node\TreeNodeInterface;
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
    private $document = null;

    /**
     * @var TreeNodeInterface
     */
    private $node = null;

    /**
     * @var ElementVersion
     */
    private $elementVersion = null;

    /**
     * @var string
     */
    private $language = null;

    /**
     * @param DocumentInterface $document
     * @param TreeNodeInterface $node
     * @param ElementVersion    $elementVersion
     * @param string            $language
     */
    public function __construct(DocumentInterface $document,
                                TreeNodeInterface $node,
                                ElementVersion $elementVersion,
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
     * @return TreeNodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return ElementVersion
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