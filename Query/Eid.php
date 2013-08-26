<?php
/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * EID Query
 *
 * @category    Makeweb
 * @package     Makeweb_IndexerElements
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_IndexerElements_Query_Eid extends MWF_Core_Indexer_Query_Abstract
{
    /**
     * @var array
     */
    protected $_fields = array('eid');

    /**
     * @var array
     */
    protected $_documentTypes = array('elements');

    /**
     * @var string
     */
    protected $_label = 'EID search';

    public function parseInput($input)
    {
        $this->setFilters(
            array(
                'eid' => (integer)$input
            )
        );
        return $this;
    }
}