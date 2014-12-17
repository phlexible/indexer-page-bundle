<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

/**
 * Content cleaner
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class ContentCleaner implements ContentCleanerInterface
{
    /**
     * {@inheritdoc}
     */
    public function clean($content)
    {
        if (substr_count($content, 'noindex') % 2) {
            throw new \Exception("Found odd number of noindex directives. Seems like an opening or closing noindex is missing.");
        }

        // Remove Content between NoIndex tags
        $content = preg_replace("|<!--\s*noindex\s*-->(.*)<!--\s*/noindex\s*-->|Umsu", '', $content);

        // strip_tags may concatenate word which are logically separated
        // <ul><li>one</li><li>two</li></ul> -> onetwo
        $content = str_replace('<', ' <', $content);

        // Remove NL, CR, TABs
        $content = str_replace(array("\r", "\n", "\t"), array(' ',' ',' '), $content);

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);

        // Convert special chars to HTML-readable stuff
        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        $doc = \Zend_Search_Lucene_Document_Html::loadHTML($content, false, 'UTF-8');
        $content = $doc->getFieldUtf8Value('body');

        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Remove multiple whitespaces
        $content = preg_replace('|\s+|u', ' ', $content);

        return $content;
    }
}
