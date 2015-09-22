<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter\HtmlContentFilter;

/**
 * HTML content filter test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class HtmlContentFilterTest extends \PHPUnit_Framework_TestCase
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
        $html = "A     B";

        $filter = new HtmlContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('A B', $result);
    }

    public function testFilterTags()
    {
        $html = "<b>A</b><i>B</i>";

        $filter = new HtmlContentFilter();
        $result = $filter->filter($html);

        $this->assertSame('A B', $result);
    }
}
