<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter;

/**
 * Chain content filter
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
