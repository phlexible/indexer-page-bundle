<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

/**
 * Content cleaner interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentCleanerInterface
{
    /**
     * Clean content
     *
     * @param string $content
     *
     * @return string
     */
    public function clean($content);
}
