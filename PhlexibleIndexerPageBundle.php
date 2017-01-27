<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle;

use Phlexible\Bundle\IndexerPageBundle\DependencyInjection\Compiler\AddParametersGeneratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Page indexer bundle.
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class PhlexibleIndexerPageBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddParametersGeneratorPass());
    }
}
