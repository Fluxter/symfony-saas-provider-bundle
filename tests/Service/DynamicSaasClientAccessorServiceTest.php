<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Service;

use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\TestCase;

class DynamicSaasClientAccessorServiceTest extends TestCase
{
    private DynamicSaasClientAccessorService $accessor;

    protected function setUp(): void
    {
        $tenant = (new Tenant('tenant-a.example.com'))->setName('Tenant A');

        $tenantService = $this->createStub(TenantService::class);
        $tenantService->method('getTenant')->willReturn($tenant);

        $this->accessor = new DynamicSaasClientAccessorService();
        $this->accessor->setClientService($tenantService);
    }

    public function testProxiesGetters(): void
    {
        $this->assertSame('tenant-a.example.com', $this->accessor->url());
        $this->assertSame('Tenant A', $this->accessor->name());
    }

    public function testProxiesIssers(): void
    {
        $this->assertTrue($this->accessor->active());
    }

    public function testThrowsOnUnknownProperty(): void
    {
        $this->expectExceptionMessage('Unknown Settings function / variable: unknown!');
        $this->accessor->unknown();
    }
}
