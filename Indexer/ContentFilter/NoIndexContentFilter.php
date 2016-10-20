<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerPageBundle\Exception\LogicException;

/**
 * No index content filter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class NoIndexContentFilter implements ContentFilterInterface
{
    /**
     * @var string
     */
    private $noIndexComment;

    /**
     * @param string $noIndexComment
     */
    public function __construct($noIndexComment = 'noindex')
    {
        $this->noIndexComment = $noIndexComment;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($content)
    {
        if (substr_count($content, $this->noIndexComment) % 2) {
            throw new LogicException('Found odd number of noindex directives. Seems like an opening or closing noindex is missing.');
        }

        // Remove Content between NoIndex tags
        $content = preg_replace("|<!--\s*{$this->noIndexComment}\s*-->(.*)<!--\s*/{$this->noIndexComment}\s*-->|Umsu", '', $content);

        return $content;
    }
}
