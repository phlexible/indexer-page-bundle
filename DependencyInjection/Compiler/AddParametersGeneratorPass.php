<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add parameters generator pass.
 *
 * @author Jens-Daniel Schulze <jdschulze@brainbits.net>
 * @author Stephan Wentz <sw@brainbits.net>
 */
class AddParametersGeneratorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $generators = array();
        foreach ($container->findTaggedServiceIds('phlexible_indexer_page.param_generator') as $id => $ttributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $generators[$priority][] = new Reference($id);
        }

        if (empty($generators)) {
            return;
        }

        krsort($generators);
        $generators = call_user_func_array('array_merge', $generators);

        $container->getDefinition('phlexible_indexer_page.parameters_generator')->replaceArgument(0, $generators);
    }
}
