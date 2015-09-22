<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentTitleExtractor;

/**
 * Content title extractor
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
