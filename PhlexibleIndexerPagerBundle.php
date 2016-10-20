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

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Page indexer bundle.
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class PhlexibleIndexerPageBundle extends Bundle
{
    public function getSiterootProperties()
    {
        // @TODO: implement skip/boost for unique IDs

        return array(
            'indexer.elements.enabled',
            'indexer.elements.skip.restricted',
            'indexer.elements.skip.elementtypeids',
            'indexer.elements.skip.tids',
            'indexer.elements.boost.elementtypeids',
            'indexer.elements.boost.tids',
        );
    }
}
