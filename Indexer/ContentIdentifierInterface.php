<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Content identifier interface
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
     * Return all identifiers
     *
     * @return DocumentDescriptor[]
     */
    public function findAllDescriptors();
}
