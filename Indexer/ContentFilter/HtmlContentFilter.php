<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer\ContentFilter;

use Symfony\Component\DomCrawler\Crawler;

/**
 * HTML content filter
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class HtmlContentFilter implements ContentFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($content)
    {
        // strip_tags may concatenate word which are logically separated
        $content = str_replace('<', ' <', $content);

        // Remove NL, CR, TABs
        $content = str_replace(array("\r", "\n", "\t"), array(' ',' ',' '), $content);

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);

        // Convert special chars to HTML-readable stuff
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        $crawler = new Crawler($content);
        $content = $crawler->filter('body')->text();

        //$doc = \Zend_Search_Lucene_Document_Html::loadHTML($content, false, 'UTF-8');
        //$content = $doc->getFieldUtf8Value('body');

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);

        return trim($content);
    }
}
