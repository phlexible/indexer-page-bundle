<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\NoIndexContentFilter;
use PHPUnit\Framework\TestCase;

/**
 * No index content filter test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\NoIndexContentFilter
 */
class NoIndexContentFilterTest extends TestCase
{
    public function testFilterNoIndex()
    {
        $html = 'A<!--noindex-->B<!--/noindex-->C';

        $filter = new NoIndexContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('AC', $result);
    }
}
