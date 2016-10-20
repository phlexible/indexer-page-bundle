<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\ContentTitleExtractor;

/**
 * Content title extractor.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentTitleExtractor implements ContentTitleExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function extractTitle($content)
    {
        // Find Title
        $match = array();
        preg_match('#<h1.*?\>(.*?)\</h1\>#u', $content, $match);

        if (!isset($match[1]) || !trim(strip_tags($match[1]))) {
            preg_match('#<h2.*?\>(.*?)\</h2\>#u', $content, $match);
        }

        if (!isset($match[1]) || !trim(strip_tags($match[1]))) {
            preg_match('#<title.*?\>(.*?)\</title\>#u', $content, $match);
        }

        if (!isset($match[1]) || !trim(strip_tags($match[1]))) {
            return null;
        }

        return trim(strip_tags($match[1]));
    }
}
