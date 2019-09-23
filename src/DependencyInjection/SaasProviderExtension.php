<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\DependencyInjection;

use Fluxter\SaasProviderBundle\Model\SaasProviderServiceInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SaasProviderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.yaml');

        $configuration = new SaasProviderConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        /*
        $definition = $container->getDefinition(SaasProviderServiceInterface::class);
        $definition->replaceArgument('client_entity', $config['client_entity']);
        */

        $container->setParameter('fluxter.saasprovider.cliententity', $config['client_entity']);
        $container->setParameter('fluxter.saasprovider.apikey', $config['apikey']);
    }
}
