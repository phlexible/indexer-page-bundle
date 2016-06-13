<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Document;

use Phlexible\Bundle\IndexerBundle\Document\Document;

/**
 * Media document
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class ElementDocument extends Document
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setFields(
            array(
                'title'         => array('type' => self::TYPE_STRING, 'copyTo' => array('autocomplete', 'didYouMean')),
                'tags'          => array('type' => self::TYPE_TEXT),
                'content'       => array('type' => self::TYPE_STRING, 'copyTo' => array('autocomplete', 'didYouMean')),
                'meta'          => array('type' => self::TYPE_TEXT, 'stored' => true),
                'language'      => array('type' => self::TYPE_STRING),
                'nodeId'        => array('type' => self::TYPE_INTEGER),
                'typeId'        => array('type' => self::TYPE_INTEGER),
                'elementtypeId' => array('type' => self::TYPE_STRING),
                'elementtype'   => array('type' => self::TYPE_STRING),
                'siterootId'    => array('type' => self::TYPE_STRING, 'indexed' => false),
                'siteroot'      => array('type' => self::TYPE_STRING),
                'navigation'    => array('type' => self::TYPE_BOOLEAN),
                'autocomplete'  => array('type' => self::TYPE_STRING, 'analyzer' => 'autocomplete', 'stored' => true, 'indexed' => true),
                'didYouMean'    => array('type' => self::TYPE_STRING, 'analyzer' => 'didYouMean', 'stored' => true, 'indexed' => true),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'element';
    }
}
