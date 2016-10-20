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

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPagerBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerPagerBundle\IndexerElementEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Element document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DocumentMapper implements DocumentMapperInterface
{
    /**
     * @var IndexibleVoterInterface
     */
    private $voter;

    /**
     * @var DocumentApplierInterface
     */
    private $applier;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param IndexibleVoterInterface  $voter
     * @param DocumentApplierInterface $applier
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        IndexibleVoterInterface $voter,
        DocumentApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->voter = $voter;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDocument(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->voter->isIndexible($descriptor)) {
            return false;
        }

        try {
            $this->applyDescriptor($document, $descriptor);
            $this->applier->apply($document, $descriptor);
        } catch (\Exception $e) {
            $this->logger->error('mapIdentity() exception: '.$e->getMessage());

            throw $e;
        }

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerElementEvents::MAP_DOCUMENT, $event);

        return true;
    }

    /**
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     */
    private function applyDescriptor(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        $document->setIdentity($descriptor->getIdentity());
        $document->set('language', $descriptor->getLanguage());
        $document->set('nodeId', $descriptor->getNode()->getId());
        $document->set('typeId', $descriptor->getNode()->getTypeId());
        $document->set('siterootId', $descriptor->getSiteroot()->getId());
        $document->set('navigation', $descriptor->getNode()->getInNavigation() ? true : false);
    }
}
