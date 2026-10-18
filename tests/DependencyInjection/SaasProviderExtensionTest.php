<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\DependencyInjection;

use Fluxter\SaasProviderBundle\Command\CreateClientCommand;
use Fluxter\SaasProviderBundle\DependencyInjection\SaasProviderExtension;
use Fluxter\SaasProviderBundle\EventSubscriber\ClientSubscriber;
use Fluxter\SaasProviderBundle\EventSubscriber\TenantChildEntitySubscriber;
use Fluxter\SaasProviderBundle\Form\Extension\ClientEntityTypeExtension;
use Fluxter\SaasProviderBundle\Form\Type\TenantChildType;
use Fluxter\SaasProviderBundle\Form\TypeGuesser\TenantChildEntityTypeGuesser;
use Fluxter\SaasProviderBundle\Security\TenantChildVoter;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SaasProviderExtensionTest extends TestCase
{
    public function testAliasIsSaasProvider(): void
    {
        $this->assertSame('saas_provider', (new SaasProviderExtension())->getAlias());
    }

    public function testExceptionThrownIfClientEntityNotSet(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->load([]);
    }

    public function testExceptionThrownIfClientEntityEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->load(['client_entity' => '']);
    }

    public function testExceptionThrownOnUnknownOption(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->load(['client_entity' => Tenant::class, 'apikey' => 'test']);
    }

    public function testParametersAreSet(): void
    {
        $container = $this->load(['client_entity' => Tenant::class, 'exclude_routes' => ['^public_', '^_profiler']]);

        $this->assertSame(Tenant::class, $container->getParameter('saas_provider.client_entity'));
        $this->assertSame(['^public_', '^_profiler'], $container->getParameter('saas_provider.exclude_routes'));
    }

    public function testExcludeRoutesDefaultsToEmptyList(): void
    {
        $container = $this->load(['client_entity' => Tenant::class]);

        $this->assertSame([], $container->getParameter('saas_provider.exclude_routes'));
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $tags
     */
    #[DataProvider('provideServices')]
    public function testServicesAreRegistered(string $id, array $tags, bool $public): void
    {
        $container = $this->load(['client_entity' => Tenant::class]);

        $this->assertTrue($container->hasDefinition($id), "Service $id is not registered");
        $definition = $container->getDefinition($id);
        $this->assertTrue($definition->isAutowired(), "Service $id is not autowired");
        $this->assertSame($public, $definition->isPublic(), "Visibility of service $id");
        foreach ($tags as $tag => $attributes) {
            $this->assertSame($attributes, $definition->getTag($tag), "Tag $tag of service $id");
        }
    }

    public static function provideServices(): iterable
    {
        yield 'command' => [CreateClientCommand::class, [], false];
        yield 'tenant service' => [TenantService::class, [], true];
        yield 'dynamic accessor' => [DynamicSaasClientAccessorService::class, [], true];
        yield 'form type' => [TenantChildType::class, ['form.type' => [[]]], false];
        yield 'form type extension' => [ClientEntityTypeExtension::class, ['form.type_extension' => [[]]], false];
        yield 'form type guesser' => [TenantChildEntityTypeGuesser::class, ['form.type_guesser' => [[]]], false];
        yield 'voter' => [TenantChildVoter::class, ['security.voter' => [[]]], false];
        yield 'client subscriber' => [ClientSubscriber::class, ['kernel.event_subscriber' => [[]]], false];
        yield 'tenant child listener' => [TenantChildEntitySubscriber::class, ['doctrine.event_listener' => [['event' => 'prePersist']]], false];
    }

    public function testCommandIsAutoconfigured(): void
    {
        $container = $this->load(['client_entity' => Tenant::class]);

        $this->assertTrue($container->getDefinition(CreateClientCommand::class)->isAutoconfigured());
    }

    public function testClientEntityIsInjectedAsParameter(): void
    {
        $container = $this->load(['client_entity' => Tenant::class]);

        foreach ([CreateClientCommand::class, TenantService::class] as $id) {
            $this->assertSame('%saas_provider.client_entity%', $container->getDefinition($id)->getArgument('$saasClientEntity'));
        }

        $this->assertSame('%saas_provider.exclude_routes%', $container->getDefinition(ClientSubscriber::class)->getArgument('$excludeRoutes'));
    }

    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new SaasProviderExtension())->load([$config], $container);

        return $container;
    }
}
