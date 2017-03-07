<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;

/**
 * Base document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class BaseDocumentMapper implements PageDocumentMapperInterface
{
    public function mapDocument(DocumentInterface $document, PageDocumentDescriptor $descriptor)
    {
        $document->setIdentity($descriptor->getIdentity());
        $document->set('language', $descriptor->getLanguage());
        $document->set('nodeId', $descriptor->getNode()->getId());
        $document->set('typeId', $descriptor->getNode()->getTypeId());
        $document->set('siterootId', $descriptor->getSiteroot()->getId());
        $document->set('navigation', $descriptor->getNode()->getInNavigation() ? true : false);
    }

}
