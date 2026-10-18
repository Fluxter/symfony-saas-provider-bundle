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

class TenantServiceTest extends TestCase
{
    #[DataProvider('provideHosts')]
    public function testGetCurrentHttpHost(string $uri, string $expectedHost): void
    {
        $service = new TenantService($this->createStub(EntityManagerInterface::class), $this->requestStack($uri), Tenant::class);

        $this->assertSame($expectedHost, $service->getCurrentHttpHost());
    }

    public static function provideHosts(): iterable
    {
        yield 'with port' => ['http://localhost:8000/foo', 'localhost'];
        yield 'https' => ['https://test.test.de/', 'test.test.de'];
        yield 'upper case' => ['http://Tenant-A.Example.COM/', 'tenant-a.example.com'];
        yield 'upper case with port' => ['https://Tenant-A.Example.COM:8443/', 'tenant-a.example.com'];
    }

    public function testGetTenantFindsClientByHost(): void
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

        $service = new TenantService($em, $this->requestStack('https://Tenant-A.example.com:8443/dashboard'), Tenant::class);

        $this->assertSame($tenant, $service->getTenant());
    }

    public function testGetTenantReturnsNullForUnknownHost(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $service = new TenantService($em, $this->requestStack('http://unknown.example.com/'), Tenant::class);

        $this->assertNull($service->getTenant());
    }

    public function testGetTenantReturnsNullWithoutRequest(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');

        $service = new TenantService($em, new RequestStack(), Tenant::class);

        $this->assertNull($service->getTenant());
    }

    public function testCreateClient(): void
    {
        $parameters = [new Note('first'), new Note('second')];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(static fn ($client) => $client instanceof Tenant && $client->getParameters() === $parameters));
        $em->expects($this->once())->method('flush');

        $service = new TenantService($em, new RequestStack(), Tenant::class);
        $client = $service->createClient($parameters);

        $this->assertInstanceOf(Tenant::class, $client);
        $this->assertSame($parameters, $client->getParameters());
    }

    private function requestStack(string $uri): RequestStack
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create($uri));

        return $requestStack;
    }
}
