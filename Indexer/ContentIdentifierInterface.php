<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer;

use Generator;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Content identifier interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface ContentIdentifierInterface
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
     * @return DocumentDescriptor
     */
    public function createDescriptorFromNode(TreeNodeInterface $node, $language);

    /**
     * @param DocumentIdentity $identity
     *
     * @return DocumentDescriptor
     */
    public function createDescriptorFromIdentity(DocumentIdentity $identity);

    /**
     * Return all identifiers.
     *
     * @return Generator|DocumentDescriptor[]
     */
    public function findAllDescriptors();
}
