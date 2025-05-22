<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\ConfigurationNode;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\ConfigurationNodeInterface;
use Netgen\Layouts\Utils\BackwardsCompatibility\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

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
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $node;
    }
}
