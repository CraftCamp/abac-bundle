<?php

namespace CraftCamp\AbacBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('craftcamp_abac');
        $rootNode
            ->children()
                ->arrayNode('configuration_files')->isRequired()->cannotBeEmpty()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('cache_options')
                    ->children()
                        ->scalarNode('cache_folder')->end()
                    ->end()
                ->end()
                ->arrayNode('attribute_options')
                    ->children()
                        ->scalarNode('getter_prefix')->end()
                        ->scalarNode('getter_name_transformation_function')->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
