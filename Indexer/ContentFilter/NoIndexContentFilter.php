<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter;

/**
 * No index content filter
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
            throw new \Exception("Found odd number of noindex directives. Seems like an opening or closing noindex is missing.");
        }

        // Remove Content between NoIndex tags
        $content = preg_replace("|<!--\s*{$this->noIndexComment}\s*-->(.*)<!--\s*/{$this->noIndexComment}\s*-->|Umsu", '', $content);

        return $content;
    }
}
