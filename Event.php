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
 * Makeweb IndexerElements Events
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Stephan Wentz <sw@brainbits.net>
 * @copyright   2007 brainbits GmbH (http://www.brainbits.net)
 */
interface Makeweb_IndexerElements_Event
{
    /**
     * Map Document Event
     * Fired when a document is mapped
     */
    const MAP_DOCUMENT = 'indexerelements.map_document';
}
