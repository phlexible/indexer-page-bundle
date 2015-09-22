<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter;

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
