<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Tests\Indexer\ContentTitleExtractor;

use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentTitleExtractor\ContentTitleExtractor;

/**
 * Content title extractor
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentTitleExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractWithH1()
    {
        $html = '<h1>testH1</h1>';

        $extractor = new ContentTitleExtractor();
        $result = $extractor->extractTitle($html);

        $this->assertSame('testH1', $result);
    }

    public function testExtractWithH2()
    {
        $html = '<h2>testH2</h2>';

        $extractor = new ContentTitleExtractor();
        $result = $extractor->extractTitle($html);

        $this->assertSame('testH2', $result);
    }

    public function testExtractWithTitle()
    {
        $html = '<title>testTitle</title>';

        $extractor = new ContentTitleExtractor();
        $result = $extractor->extractTitle($html);

        $this->assertSame('testTitle', $result);
    }

    public function testExtractWithNoMatch()
    {
        $html = 'testNull';

        $extractor = new ContentTitleExtractor();
        $result = $extractor->extractTitle($html);

        $this->assertNull($result);
    }
}
