<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\DependencyInjection;

use Fluxter\SaasProviderBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testMinimalConfiguration(): void
    {
        $this->assertSame(
            ['client_entity' => 'App\Entity\Tenant', 'exclude_routes' => []],
            $this->process([['client_entity' => 'App\Entity\Tenant']]),
        );
    }

    public function testExcludeRoutes(): void
    {
        $config = $this->process([['client_entity' => 'App\Entity\Tenant', 'exclude_routes' => ['^public_', '^_wdt']]]);

        $this->assertSame(['^public_', '^_wdt'], $config['exclude_routes']);
    }

    public function testLaterConfigsOverrideClientEntity(): void
    {
        $config = $this->process([
            ['client_entity' => 'App\Entity\Tenant'],
            ['client_entity' => 'App\Entity\Customer'],
        ]);

        $this->assertSame('App\Entity\Customer', $config['client_entity']);
    }

    public function testExcludeRoutesMustBeScalars(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->process([['client_entity' => 'App\Entity\Tenant', 'exclude_routes' => [['nested']]]]);
    }

    private function process(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }
}
