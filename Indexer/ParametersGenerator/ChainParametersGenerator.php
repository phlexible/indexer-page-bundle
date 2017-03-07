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
 * Merging chaing parameters generator.
 *
 * @author Jens-Daniel Schulze <jdschulze@brainbits.net>
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainParametersGenerator implements IndexerParametersGeneratorInterface
{
    /**
     * @var IndexerParametersGeneratorInterface[]
     */
    private $parametersGenerators = [];

    /**
     * @param IndexerParametersGeneratorInterface[] $parameterGenerators
     */
    public function __construct(array $parameterGenerators = array())
    {
        foreach ($parameterGenerators as $parameterGenerator) {
            $this->add($parameterGenerator);
        }
    }

    private function add(IndexerParametersGeneratorInterface $parametersGenerator)
    {
        $this->parametersGenerators[] = $parametersGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function createParameters(PageDocumentDescriptor $identity)
    {
        $parameters = array();

        foreach ($this->parametersGenerators as $generator) {
            $parameters = array_merge($parameters, $generator->createParameters($identity));
        }

        return $parameters;
    }
}
