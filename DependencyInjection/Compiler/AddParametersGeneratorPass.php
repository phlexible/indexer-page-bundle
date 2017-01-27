<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\BUndle\IndexerPageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddParametersGeneratorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $generators = array();
        foreach ($container->findTaggedServiceIds('phlexible_indexer_element.param_generator') as $id => $ttributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $generators[$priority][] = new Reference($id);
        }

        if (empty($generators)) {
            return;
        }

        krsort($generators);
        $generators = call_user_func_array('array_merge', $generators);

        $container->getDefinition('phlexible_indexer_element.parameters_generator')->replaceArgument(0, $generators);
    }
}
