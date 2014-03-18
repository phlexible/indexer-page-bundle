<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementsComponent;

use Phlexible\Component\AbstractComponent;

/**
 * Elements indexer component
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class IndexerElementsComponent extends AbstractComponent
{
    public function __construct()
    {
        $this
            ->setVersion('0.7.1')
            ->setId('indexerelements')
            ->setPackage('phlexible');
    }

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
