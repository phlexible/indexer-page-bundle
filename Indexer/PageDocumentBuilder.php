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

use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Phlexible\Bundle\IndexerPageBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\PageDocumentMapperInterface;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page document builder.
 *
 * @author Phillip Look <pl@brainbits.net>
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PageDocumentBuilder
{
    /**
     * @var PageDocumentFactory
     */
    private $documentFactory;

    /**
     * @var PageDocumentMapperInterface
     */
    private $mapper;

    /**
     * @var IndexibleVoterInterface
     */
    private $indexibleVoter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param PageDocumentFactory         $documentFactory
     * @param PageDocumentMapperInterface $mapper
     * @param IndexibleVoterInterface     $indexibleVoter
     * @param EventDispatcherInterface    $dispatcher
     */
    public function __construct(
        PageDocumentFactory $documentFactory,
        PageDocumentMapperInterface $mapper,
        IndexibleVoterInterface $indexibleVoter,
        EventDispatcherInterface $dispatcher
    ) {
        $this->documentFactory = $documentFactory;
        $this->mapper = $mapper;
        $this->indexibleVoter = $indexibleVoter;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param PageDocumentDescriptor $descriptor
     *
     * @return null|PageDocument
     */
    public function build(PageDocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->indexibleVoter->isIndexible($descriptor)) {
            return null;
        }

        $document = $this->createDocument();

        $this->mapper->mapDocument($document, $descriptor);

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerPageEvents::MAP_DOCUMENT, $event);

        return $document;
    }

    /**
     * @return PageDocument
     */
    public function createDocument()
    {
        return $this->documentFactory->createDocument();
    }
}
