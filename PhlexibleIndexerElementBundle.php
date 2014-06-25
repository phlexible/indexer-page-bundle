<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Element indexer bundle
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class PhlexibleIndexerElementBundle extends Bundle
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
