<?php
/**
 * MWF - MAKEweb Framework
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 * @version     SVN: $Id: Generator.php 2312 2007-01-25 18:46:27Z swentz $
 */

/**
 * Makeweb IndexerElements Map Document Event
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Stephan Wentz <sw@brainbits.net>
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_IndexerElements_Event_MapDocument extends Brainbits_Event_Notification_Abstract
{
    /**
     * @var string
     */
    protected $_notificationName = Makeweb_IndexerElements_Event::MAP_DOCUMENT;

    /**
     * @var MWF_Core_Indexer_Indexer_Interface
     */
    protected $_document = null;

    /**
     * @var Makeweb_Elements_Tree_Node
     */
    protected $_node = null;

    /**
     * @var Makeweb_Elements_Element_Version
     */
    protected $_elementVersion = null;

    /**
     * @var string
     */
    protected $_language = null;

    /**
     * Constructor
     *
     * @param MWF_Core_Indexer_Indexer_Interface $document
     * @param Makeweb_Elements_Tree_Node         $node
     * @param Makeweb_Elements_Element_Version   $elementVersion
     * @param string                             $language
     */
    public function __construct(MWF_Core_Indexer_Indexer_Interface $document,
                                Makeweb_Elements_Tree_Node $node,
                                Makeweb_Elements_Element_Version $elementVersion,
                                $language)
    {
        $this->_document       = $document;
        $this->_node           = $node;
        $this->_elementVersion = $elementVersion;
        $this->_language       = $language;
    }

    /**
     * Return document
     *
     * @return MWF_Core_Indexer_Document_Interface
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * Return node
     *
     * @return Makeweb_Elements_Tree_Node
     */
    public function getNode()
    {
        return $this->_node;
    }

    /**
     * Return element version
     *
     * @return Makeweb_Elements_Element_Version
     */
    public function getElementVersion()
    {
        return $this->_elementVersion;
    }

    /**
     * Return language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }
}