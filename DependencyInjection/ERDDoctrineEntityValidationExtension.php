<?php

namespace ERD\DoctrineEntityValidationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ERDDoctrineEntityValidationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        //get the service to modify
        $id = 'erd_doctrine_entity_validation.validation_subscriber';
        $def = $container->getDefinition($id);
            
        //add classes to exclude if any
        if(isset($config['dont_validate']) && count($config['dont_validate']) > 0)
        {
            $args = $def->getArguments();
            $args[1] = $config['dont_validate'];
        
            $def->setArguments($args);
        }
            
        //set the connection attribute, if we're limiting to one connection
        if(isset($config['connection']) && $config['connection'])
        {  
            $tags = $def->getTags();
            $tags['doctrine.event_subscriber'][0]['connection'] = $config['connection'];
            $def->setTags($tags);
        }
            
        //readd to container
        $container->setDefinition($id, $def);
    }
}