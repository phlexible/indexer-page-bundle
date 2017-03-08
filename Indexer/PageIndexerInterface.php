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

use Phlexible\Bundle\IndexerBundle\Indexer\IndexerInterface;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Page indexer interface.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
interface PageIndexerInterface extends IndexerInterface
{
    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function addNode(TreeNodeInterface $node, $language, $viaQueue = false);

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function updateNode(TreeNodeInterface $node, $language, $viaQueue = false);

    /**
     * @param TreeNodeInterface $node
     * @param string            $language
     * @param bool              $viaQueue
     *
     * @return bool
     */
    public function deleteNode(TreeNodeInterface $node, $language, $viaQueue = false);
}
