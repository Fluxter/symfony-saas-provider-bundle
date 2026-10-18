<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class TenantServiceTest extends TestCase
{
    #[DataProvider('provideHosts')]
    public function testGetCurrentHttpHost(string $uri, string $expectedHost): void
    {
        $service = $this->service($this->createStub(EntityManagerInterface::class), $this->requestStack($uri));

        $this->assertSame($expectedHost, $service->getCurrentHttpHost());
    }

    public static function provideHosts(): iterable
    {
        yield 'with port' => ['http://localhost:8000/foo', 'localhost'];
        yield 'https' => ['https://test.test.de/', 'test.test.de'];
        yield 'upper case' => ['http://Tenant-A.Example.COM/', 'tenant-a.example.com'];
        yield 'upper case with port' => ['https://Tenant-A.Example.COM:8443/', 'tenant-a.example.com'];
    }

    public function testGetCurrentHttpHostWithoutRequest(): void
    {
        $this->assertNull($this->service($this->createStub(EntityManagerInterface::class), new RequestStack())->getCurrentHttpHost());
    }

    public function testGetTenantFindsClientByHostAndCachesIt(): void
    {
        $tenant = new Tenant('tenant-a.example.com');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['url' => 'tenant-a.example.com'])
            ->willReturn($tenant);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Tenant::class)
            ->willReturn($repository);

        $service = $this->service($em, $this->requestStack('https://Tenant-A.example.com:8443/dashboard'));

        $this->assertSame($tenant, $service->getTenant());
        $this->assertSame($tenant, $service->getTenant());
    }

    public function testResetForgetsTheCachedTenant(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(new Tenant('tenant-a.example.com'));

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = $this->service($em, $this->requestStack('http://tenant-a.example.com/'));
        $service->getTenant();
        $service->reset();
        $service->getTenant();
    }

    public function testSetTenantOverridesDiscovery(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');

        $tenant = new Tenant('console.example.com');
        $service = $this->service($em, new RequestStack());

        $this->assertSame($service, $service->setTenant($tenant));
        $this->assertSame($tenant, $service->getTenant());
    }

    public function testGetTenantReturnsNullForUnknownHost(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = $this->service($em, $this->requestStack('http://unknown.example.com/'));

        $this->assertNull($service->getTenant());
    }

    public function testGetTenantReturnsNullWithoutRequest(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');

        $this->assertNull($this->service($em, new RequestStack())->getTenant());
    }

    public function testCreateClient(): void
    {
        $parameters = [new Note('first'), new Note('second')];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(static fn ($client) => $client instanceof Tenant && $client->getParameters() === $parameters));
        $em->expects($this->once())->method('flush');

        $client = $this->service($em, new RequestStack())->createClient($parameters);

        $this->assertInstanceOf(Tenant::class, $client);
        $this->assertSame($parameters, $client->getParameters());
    }

    public function testSetAndResetRouterContext(): void
    {
        $context = new RequestContext('', 'GET', 'global.example.com', 'http', 80, 443);

        $router = $this->createMock(RouterInterface::class);
        $router->method('getContext')->willReturn($context);
        $router->expects($this->once())
            ->method('setContext')
            ->with($this->callback(static fn (RequestContext $original) => 'global.example.com' === $original->getHost()));

        $service = $this->service($this->createStub(EntityManagerInterface::class), $this->requestStack('https://foo.example.com:8443/'), $router);
        $service->setRouterContext(new Tenant('tenant-a.example.com'));

        $this->assertSame('tenant-a.example.com', $context->getHost());
        $this->assertSame('https', $context->getScheme());
        $this->assertSame(8443, $context->getHttpsPort());

        $service->resetRouterContext();
        // A second reset does nothing
        $service->resetRouterContext();
    }

    private function service(EntityManagerInterface $em, RequestStack $requestStack, ?RouterInterface $router = null): TenantService
    {
        return new TenantService($em, $requestStack, $router ?? $this->createStub(RouterInterface::class), $this->createStub(KernelInterface::class), Tenant::class);
    }

    private function requestStack(string $uri): RequestStack
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create($uri));

        return $requestStack;
    }
}
