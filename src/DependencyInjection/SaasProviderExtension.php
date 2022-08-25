<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SaasProviderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $excludeRoutes = array_key_exists('exclude_routes', $config) ? $config['exclude_routes'] : null;
        if (null != $excludeRoutes) {
            $container->setParameter('saas_provider.exclude_routes', $config['exclude_routes']);
        }

        $container->setParameter('saas_provider.client_entity', $config['client_entity']);
        if (array_key_exists("global_url", $config)) {
            $container->setParameter('saas_provider.global_url', $config['global_url']);
        }
    }
}
