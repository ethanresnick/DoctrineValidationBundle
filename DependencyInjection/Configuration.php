<?php

namespace ERD\DoctrineEntityValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('erd_doctrine_entity_validation');
        $rootNode
            ->children()
                //general
                ->arrayNode('dont_validate')->prototype('scalar')->end()->end() //each item is a scalar
                ->scalarNode('connection')->end() //an empty value means all connections.
            ->end()
        ;

        return $treeBuilder;
    }
}