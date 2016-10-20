<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\ContentTitleExtractor;

use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentTitleExtractor\ContentTitleExtractor;

/**
 * Content title extractor.
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
