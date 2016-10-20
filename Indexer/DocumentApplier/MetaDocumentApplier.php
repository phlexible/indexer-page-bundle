<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\ElementBundle\Meta\ElementMetaDataManager;
use Phlexible\Bundle\ElementBundle\Meta\ElementMetaSetResolver;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\DocumentDescriptor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Meta document applier
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class MetaDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var ElementMetaSetResolver
     */
    private $elementMetaSetResolver;

    /**
     * @var ElementMetaDataManager
     */
    private $elementMetaDataManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ElementService           $elementService
     * @param ElementMetaSetResolver   $elementMetaSetResolver
     * @param ElementMetaDataManager   $elementMetaDataManager
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(
        ElementService $elementService,
        ElementMetaSetResolver $elementMetaSetResolver,
        ElementMetaDataManager $elementMetaDataManager,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->elementService = $elementService;
        $this->elementMetaSetResolver = $elementMetaSetResolver;
        $this->elementMetaDataManager = $elementMetaDataManager;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;

    }

    /**
     * @param DocumentInterface $document
     * @param DocumentDescriptor   $descriptor
     */
    public function apply(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $language = $descriptor->getLanguage();

        $element        = $this->elementService->findElement($node->getTypeId());
        $elementVersion = $this->elementService->findElementVersion($element, $node->getTree()->getPublishedVersion($node, $language));

        $metaSet = $this->elementMetaSetResolver->resolve($elementVersion);

        if (!$metaSet) {
            return;
        }

        $metaData = $this->elementMetaDataManager->findByMetaSetAndElementVersion($metaSet, $elementVersion);

        if (!$metaData) {
            return;
        }

        $values = $metaData->getValues();

        if (!isset($values[$language])) {
            return;
        }

        $meta = array();
        foreach ($values[$language] as $value) {
            $value = trim($value);
            if ($value) {
                $meta[] = trim($value);
            }
        }

        $document->set('meta', $meta);
    }
}
