<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementBundle;

/**
 * Elements indexer events
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface IndexerElementEvents
{
    /**
     * Map Document Event
     * Fired when a document is mapped
     */
    const MAP_DOCUMENT = 'phlexible_indexer_element.map_document';
}
