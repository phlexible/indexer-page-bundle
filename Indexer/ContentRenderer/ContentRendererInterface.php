<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\ContentRenderer;

use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;

/**
 * Content renderer interface
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentRendererInterface
{
    /**
     * Load a html representation of an element.
     *
     * @param DocumentDescriptor $descriptor
     *
     * @return string
     * @throws \Exception
     */
    public function render(DocumentDescriptor $descriptor);
}
