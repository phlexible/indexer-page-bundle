<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests;

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;

/**
 * Page descriptor trait.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
trait PageDescriptorTrait
{
    /**
     * @return PageDocumentDescriptor
     */
    protected function createDescriptor()
    {
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $node->setInNavigation(true);
        $siteroot = new Siteroot(345);
        $language = 'de';

        return new PageDocumentDescriptor(new DocumentIdentity('abc'), $node, $siteroot, $language);
    }
}
