<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
