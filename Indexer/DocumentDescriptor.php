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

use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\Model\TreeNodeInterface;

/**
 * Document descriptor.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DocumentDescriptor
{
    /**
     * @var DocumentIdentity
     */
    private $identity;

    /**
     * @var TreeNodeInterface
     */
    private $node;

    /**
     * @var Siteroot
     */
    private $siteroot;

    /**
     * @var string
     */
    private $language;

    /**
     * @param DocumentIdentity  $identity
     * @param TreeNodeInterface $node
     * @param Siteroot          $siteroot
     * @param string            $language
     */
    public function __construct(DocumentIdentity $identity, TreeNodeInterface $node, Siteroot $siteroot, $language)
    {
        $this->identity = $identity;
        $this->node = $node;
        $this->siteroot = $siteroot;
        $this->language = $language;
    }

    /**
     * @return DocumentIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return TreeNodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return Siteroot
     */
    public function getSiteroot()
    {
        return $this->siteroot;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
