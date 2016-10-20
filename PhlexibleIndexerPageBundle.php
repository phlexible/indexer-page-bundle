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
            'indexer.page.enabled',
            'indexer.page.skip.restricted',
            'indexer.page.skip.elementtypeids',
            'indexer.page.skip.tids',
            'indexer.page.boost.elementtypeids',
            'indexer.page.boost.tids',
        );
    }
}
