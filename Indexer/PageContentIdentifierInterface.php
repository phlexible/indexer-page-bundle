<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer;

use Generator;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Page content identifier interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface PageContentIdentifierInterface
{
    /**
     * @param DocumentIdentity $identity
     *
     * @return bool
     */
    public function validateIdentity(DocumentIdentity $identity);

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     *
     * @return PageDocumentDescriptor
     */
    public function createDescriptorFromNode(TreeNodeInterface $node, $language);

    /**
     * @param DocumentIdentity $identity
     *
     * @return PageDocumentDescriptor
     */
    public function createDescriptorFromIdentity(DocumentIdentity $identity);

    /**
     * Return all identifiers.
     *
     * @return Generator|PageDocumentDescriptor[]
     */
    public function findAllDescriptors();
}
