<?php

namespace Fluxter\SaasProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;

class SaasProviderConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('saas_provider');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('client_entity')->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
