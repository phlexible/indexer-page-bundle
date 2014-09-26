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
 * @author Phillip Look <pl@brainbits.net>
 */
class ElementDocument extends Document
{
    public function __construct()
    {
        $this->setFields(
            array(
                'title'           => array('type' => self::TYPE_STRING),
                'highlight_title' => array('type' => self::TYPE_STRING, 'readonly' => true),
                'tags'            => array('type' => self::TYPE_TEXT, 'array' => true, 'readonly' => true),
                'copy'            => array('type' => self::TYPE_TEXT, 'array' => true, 'readonly' => true),
                'content'         => array('type' => self::TYPE_STRING, 'copyFields' => array('copy')),

                'language'        => array('type' => self::TYPE_STRING),
                'context'         => array('type' => self::TYPE_STRING, 'array' => true),
                'cleantitle'      => array('type' => self::TYPE_STRING, 'readonly' => true),
                'tid'             => array('type' => self::TYPE_INTEGER),
                'eid'             => array('type' => self::TYPE_INTEGER),
                'elementtype'     => array('type' => self::TYPE_STRING),
                'url'             => array('type' => self::TYPE_STRING),
                'siteroot'        => array('type' => self::TYPE_STRING),
                'navigation'      => array('type' => self::TYPE_BOOLEAN),
                'restricted'      => array('type' => self::TYPE_BOOLEAN),
            )
        );
    }
}
