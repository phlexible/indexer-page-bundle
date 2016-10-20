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
 * Chain content filter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainContentFilter implements ContentFilterInterface
{
    private $filters = array();

    /**
     * @param ContentFilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($content)
    {
        foreach ($this->filters as $filter) {
            $content = $filter->filter($content);
        }

        return $content;
    }
}
