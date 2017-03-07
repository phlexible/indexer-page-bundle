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
 * Locale parameters generator.
 *
 * @author Jens-Daniel Schulze <jdschulze@brainbits.net>
 * @author Stephan Wentz <sw@brainbits.net>
 */
class LocaleParametersGenerator implements IndexerParametersGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function createParameters(PageDocumentDescriptor $identity)
    {
        return ['_locale' => $identity->getLanguage()];
    }
}
