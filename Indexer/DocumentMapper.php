<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerElementBundle\IndexerElementEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Element document mapper
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DocumentMapper
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
     * Map node to document
     *
     * @param DocumentInterface  $document
     * @param DocumentDescriptor $descriptor
     *
     * @return DocumentInterface
     * @throws \Exception
     */
    public function mapDocument(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        if (IndexibleVoterInterface::VOTE_DENY === $this->voter->isIndexible($descriptor)) {
            return null;
        }

        try {
            $this->applyDescriptor($document, $descriptor);
            $this->applier->apply($document, $descriptor);
        } catch (\Exception $e) {
            $this->logger->error('mapIdentity() exception: ' . $e->getMessage());

            throw $e;
        }

        $event = new MapDocumentEvent($document, $descriptor);
        $this->dispatcher->dispatch(IndexerElementEvents::MAP_DOCUMENT, $event);

        return $document;
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
