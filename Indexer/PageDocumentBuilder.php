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

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper\PageDocumentMapperInterface;
use Phlexible\Bundle\IndexerPageBundle\IndexerPageEvents;
use Phlexible\Bundle\IndexerPageBundle\Document\PageDocument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page document builder.
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class PageDocumentBuilder
{
    /**
     * @var DocumentFactory
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
     * @var string
     */
    private $documentClass;

    /**
     * @param DocumentFactory             $documentFactory
     * @param PageDocumentMapperInterface $mapper
     * @param IndexibleVoterInterface     $indexibleVoter
     * @param EventDispatcherInterface    $dispatcher
     * @param string                      $documentClass
     */
    public function __construct(
        DocumentFactory $documentFactory,
        PageDocumentMapperInterface $mapper,
        IndexibleVoterInterface $indexibleVoter,
        EventDispatcherInterface $dispatcher,
        $documentClass = PageDocument::class
    ) {
        $this->documentFactory = $documentFactory;
        $this->mapper = $mapper;
        $this->indexibleVoter = $indexibleVoter;
        $this->dispatcher = $dispatcher;
        $this->documentClass = $documentClass;
    }

    /**
     * @param PageDocumentDescriptor $descriptor
     *
     * @return null|DocumentInterface
     */
    public function build(PageDocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->indexibleVoter->isIndexible($descriptor)) {
            return null;
        }

        $document = $this->documentFactory->factory($this->documentClass);

        $this->mapper->mapDocument($document, $descriptor);

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerPageEvents::MAP_DOCUMENT, $event);

        return $document;
    }
}
