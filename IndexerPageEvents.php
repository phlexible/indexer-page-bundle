<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle;

/**
 * Page indexer events.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexerPageEvents
{
    /**
     * Map Document Event
     * Fired when a document is mapped.
     */
    const MAP_DOCUMENT = 'phlexible_indexer_page.map_document';
}
