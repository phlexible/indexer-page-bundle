<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter\ChainContentFilter;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter\ContentFilterInterface;

/**
 * Chain content filter test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainContentFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterChain()
    {
        $filter1 = $this->prophesize(ContentFilterInterface::class);
        $filter2 = $this->prophesize(ContentFilterInterface::class);

        $filter1->filter('test')->willReturn('test1');
        $filter2->filter('test1')->willReturn('test2');

        $filter = new ChainContentFilter(array($filter1->reveal(), $filter2->reveal()));
        $result = $filter->filter('test');

        $this->assertSame('test2', $result);
    }
}
