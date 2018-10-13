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
                ->arrayNode('configuration_files')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
