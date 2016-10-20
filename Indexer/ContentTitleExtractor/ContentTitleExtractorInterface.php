<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer\ContentTitleExtractor;

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
