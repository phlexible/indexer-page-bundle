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
 * Chain document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainDocumentMapper implements PageDocumentMapperInterface
{
    /**
     * @var PageDocumentMapperInterface[]
     */
    private $mappers = array();

    /**
     * @param PageDocumentMapperInterface[] $mappers
     */
    public function __construct(array $mappers)
    {
        foreach ($mappers as $mapper) {
            $this->addMapper($mapper);
        }
    }

    private function addMapper(PageDocumentMapperInterface $mapper)
    {
        $this->mappers[] = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDocument(DocumentInterface $document, PageDocumentDescriptor $descriptor)
    {
        foreach ($this->mappers as $mapper) {
            $mapper->mapDocument($document, $descriptor);
        }

        return;
    }
}
