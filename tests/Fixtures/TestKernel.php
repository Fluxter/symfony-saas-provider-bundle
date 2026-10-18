<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fluxter\SaasProviderBundle\SaasProviderBundle;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal application that uses the bundle, for the functional tests.
 */
class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new SaasProviderBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/fluxter-saas-provider-bundle/' . Kernel::VERSION . '/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/fluxter-saas-provider-bundle/' . Kernel::VERSION . '/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'router' => ['utf8' => true],
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            'orm' => [
                'mappings' => [
                    'Fixtures' => [
                        'type' => 'attribute',
                        'dir' => __DIR__ . '/Entity',
                        'prefix' => 'Fluxter\SaasProviderBundle\Tests\Fixtures\Entity',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]);

        $container->extension('saas_provider', [
            'client_entity' => Tenant::class,
            'exclude_routes' => ['^public_'],
        ]);

        $services = $container->services();
        $services->set('logger', NullLogger::class);
        $services
            ->defaults()
                ->autowire()
                ->autoconfigure()
            ->load('Fluxter\SaasProviderBundle\Tests\Fixtures\\', __DIR__ . '/{Controller,Repository}');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/Controller/', 'attribute');
    }
}
