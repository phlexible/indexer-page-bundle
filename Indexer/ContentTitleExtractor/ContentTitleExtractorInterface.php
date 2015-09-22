<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentTitleExtractor;

/**
 * Content title extractor interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentTitleExtractorInterface
{
    /**
     * @param string $content
     *
     * @return string|null
     */
    public function extractTitle($content);
}
