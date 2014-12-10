<?php

namespace Ibrows\EasySysBundle\DependencyInjection;

use Ibrows\EasySysBundle\IbrowsEasySysBundle;

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
        $rootNode = $treeBuilder->root('ibrows_easy_sys');

        $rootNode
            ->children()
            ->arrayNode('connection')->addDefaultsIfNotSet()->isRequired()->cannotBeEmpty()
                ->children()
                    ->scalarNode('httpClientServiceId')->defaultValue('ibrows.easysys.connection.httpclient')->end()
                    ->scalarNode('serviceUri')->defaultValue('https://dev.easysys.ch/api2.php')->end()
                    ->scalarNode('companyName')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('apiKey')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('signatureKey')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('userId')->isRequired()->cannotBeEmpty()->end()
                ->end()
            ->end()


            ->scalarNode('handlerservice')->defaultValue('ibrows.easysys.savehandler')->end()
            ->scalarNode('throwExceptionOnAdditionalData')->defaultValue(null)->end()

            ->end()
        ;
        $classes = $rootNode
        ->children()
        ->arrayNode('classes')->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('default')->defaultNull()->end();

                    foreach(IbrowsEasySysBundle::getTypes() as $key => $typename){
                        $classes->scalarNode($typename)->defaultNull()->end();
                    }
        $classes->end()->end();


        return $treeBuilder;
    }

}