<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementBundle\EventListener;

use Phlexible\IndexerBundle\Document\DocumentInterface;
use Phlexible\IndexerBundle\Event\DocumentEvent;

/**
 * Document listener
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class DocumentListener
{
    /**
     * @param DocumentEvent $event
     */
    public function onCreateDocument(DocumentEvent $event)
    {
        $document = $event->getDocument();

        if ('elements' !== $document->getDocumentType()) {
            return;
        }

        $document->setFields(
            array(
                'title'           => array(),
                'highlight_title' => array(DocumentInterface::CONFIG_READONLY),
                'tags'            => array(DocumentInterface::CONFIG_READONLY, DocumentInterface::CONFIG_MULTIVALUE),
                'copy'            => array(DocumentInterface::CONFIG_READONLY, DocumentInterface::CONFIG_MULTIVALUE),

                'language'        => array(),
                'context'         => array(DocumentInterface::CONFIG_MULTIVALUE),
                'cleantitle'      => array(DocumentInterface::CONFIG_READONLY),
                'tid'             => array(),
                'eid'             => array(),
                'elementtype'     => array(),
                'url'             => array(),
                'siteroot'        => array(),
                'restricted'      => array(),
                'content'         => array(DocumentInterface::CONFIG_COPY),
            )
        );
    }
}
