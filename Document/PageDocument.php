<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Document;

use Phlexible\Bundle\IndexerBundle\Document\Document;

/**
 * Media document.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class PageDocument extends Document
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setFields(
            array(
                'title' => array('type' => self::TYPE_TEXT, 'copyTo' => array('autocomplete', 'didYouMean')),
                'tags' => array('type' => self::TYPE_TEXT),
                'content' => array('type' => self::TYPE_TEXT, 'copyTo' => array('autocomplete', 'didYouMean')),
                'meta' => array('type' => self::TYPE_TEXT, 'stored' => true),
                'language' => array('type' => self::TYPE_KEYWORD),
                'nodeId' => array('type' => self::TYPE_INTEGER),
                'typeId' => array('type' => self::TYPE_INTEGER),
                'elementtypeId' => array('type' => self::TYPE_KEYWORD),
                'elementtype' => array('type' => self::TYPE_KEYWORD),
                'siterootId' => array('type' => self::TYPE_KEYWORD, 'indexed' => false),
                'siteroot' => array('type' => self::TYPE_KEYWORD),
                'navigation' => array('type' => self::TYPE_BOOLEAN),
                'autocomplete' => array('type' => self::TYPE_STRING, 'analyzer' => 'autocomplete', 'stored' => true, 'indexed' => true),
                'didYouMean' => array('type' => self::TYPE_STRING, 'analyzer' => 'didYouMean', 'stored' => true, 'indexed' => true),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'page';
    }
}
