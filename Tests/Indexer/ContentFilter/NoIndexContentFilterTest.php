<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter\NoIndexContentFilter;

/**
 * No index content filter test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class NoIndexContentFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterNoIndex()
    {
        $html = 'A<!--noindex-->B<!--/noindex-->C';

        $filter = new NoIndexContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('AC', $result);
    }
}
