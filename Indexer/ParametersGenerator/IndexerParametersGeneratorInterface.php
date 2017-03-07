<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\ParametersGenerator;

use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;

/**
 * Create parameters for indexing.
 *
 * @author Jens Schulze <jdschulze@brainbits.net>
 */
interface IndexerParametersGeneratorInterface
{
    /**
     * @param PageDocumentDescriptor $identity
     *
     * @return array
     */
    public function createParameters(PageDocumentDescriptor $identity);
}
