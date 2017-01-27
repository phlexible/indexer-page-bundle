<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\BUndle\IndexerPageBundle\Indexer\ParametersGenerator;

use Phlexible\Bundle\IndexerPageBundle\Indexer\DocumentDescriptor;

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
    public function createParameters(DocumentDescriptor $identity)
    {
        $parameters = array();

        foreach ($this->parametersGenerators as $generator) {
            $parameters = array_merge($parameters, $generator->createParameters($identity));
        }

        return $parameters;
    }
}
