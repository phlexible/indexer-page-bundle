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

use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\HtmlContentFilter;
use PHPUnit\Framework\TestCase;

/**
 * HTML content filter test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\HtmlContentFilter
 */
class HtmlContentFilterTest extends TestCase
{
    public function testFilterControlCharacters()
    {
        $html = "A\nB\rC\tD";

        $filter = new HtmlContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('A B C D', $result);
    }

    public function testFilterWhitespace()
    {
        $html = 'A     B';

        $filter = new HtmlContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('A B', $result);
    }

    public function testFilterTags()
    {
        $html = '<b>A</b><i>B</i>';

        $filter = new HtmlContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('A B', $result);
    }
}
