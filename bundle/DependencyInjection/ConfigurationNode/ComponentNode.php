<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\ConfigurationNode;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\ConfigurationNodeInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class ComponentNode implements ConfigurationNodeInterface
{
    public function getConfigurationNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('ibexa_component');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->integerNode('default_parent_location')
                    ->defaultValue(2)
                ->end()
                ->arrayNode('parent_locations')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
