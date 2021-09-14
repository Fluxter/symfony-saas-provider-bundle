<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('saas_provider');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('client_entity')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('global_url')->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
