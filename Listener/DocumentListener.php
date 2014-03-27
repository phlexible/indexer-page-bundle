<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\IndexerElementsComponent\Listener;

/**
 * Document listener
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class DocumentListener
{
    public function onCreateDocument(CreateDocumentListener $event)
    {
        $document = $event->getDocument();

        if ('elements' !== $document->getDocumentType())
        {
            return;
        }

        $document->setFields(
            array(
                'title'           => array(),
                'highlight_title' => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY),
                'tags'            => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'copy'            => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY, MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),

                'language'        => array(),
                'context'         => array(MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
                'cleantitle'      => array(MWF_Core_Indexer_Document_Interface::CONFIG_READONLY),
                'tid'             => array(),
                'eid'             => array(),
                'elementtype'     => array(),
                'url'             => array(),
                'siteroot'        => array(),
                'restricted'      => array(),
                'content'         => array(MWF_Core_Indexer_Document_Interface::CONFIG_COPY),
            )
        );
    }
}
