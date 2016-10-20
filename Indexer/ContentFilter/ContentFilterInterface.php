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

/**
 * Content filter interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentFilterInterface
{
    /**
     * Filter content
     *
     * @param string $content
     *
     * @return string
     */
    public function filter($content);
}
