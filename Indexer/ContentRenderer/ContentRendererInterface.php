<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\ContentRenderer;

use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;

/**
 * Content renderer interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentRendererInterface
{
    /**
     * Load a html representation of a node.
     *
     * @param PageDocumentDescriptor $descriptor
     *
     * @return string
     */
    public function render(PageDocumentDescriptor $descriptor);
}
