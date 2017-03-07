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
 * Boost document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class BoostDocumentMapper implements PageDocumentMapperInterface
{
    public function mapDocument(DocumentInterface $document, PageDocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();

        $boostProperty = $siteroot->getProperty('page_indexer.boost_node_ids');
        $boostTids = $this->getKeyValueProperty($boostProperty);
        $tid = $node->getId();

        // 1. try boosting by tid
        if (isset($boostTids[$tid])) {
            $document->setBoost($boostTids[$tid]);

            return;
        }

        return;

        $boostProperty = $siteroot->getProperty('page_indexer.boost_elementtype_ids');
        $boostElementtypes = $this->getKeyValueProperty($boostProperty);
        $elementTypeId = $elementVersion->getElement()->getElementtypeId();

        // 2. try boosting by element type id
        if (isset($boostElementtypes[$elementTypeId])) {
            $document->setBoost($boostElementtypes[$elementTypeId]);
        }
    }

    /**
     * Parse a multivalue property 123:2;17:3.
     *
     * @param string $property
     *
     * @return array
     */
    private function getKeyValueProperty($property)
    {
        $result = array();

        // extract key/value pairs
        $valuePairs = explode(',', $property);
        foreach ($valuePairs as $valuePair) {
            // extract key/value of a single value
            $keyValue = explode(':', $valuePair);

            // key and value must be present
            if (!isset($keyValue[1]) || !isset($keyValue[0])) {
                continue;
            }

            $key = trim($keyValue[0]);
            $value = trim($keyValue[1]);

            // key and value must be present
            if (!strlen($key) || !strlen($value)) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
