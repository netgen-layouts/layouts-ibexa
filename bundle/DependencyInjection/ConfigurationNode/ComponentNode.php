<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsIbexaBundle\DependencyInjection\ConfigurationNode;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\ConfigurationNodeInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

use function is_int;
use function is_string;

final class ComponentNode implements ConfigurationNodeInterface
{
    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition<\Symfony\Component\Config\Definition\Builder\TreeBuilder<'array'>>
     */
    public function getConfigurationNode(): ArrayNodeDefinition
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
                        ->validate()
                            ->ifTrue(static fn (mixed $v): bool => !is_int($v) && !is_string($v))
                            ->thenInvalid('Parent location needs to be a string or an integer.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
