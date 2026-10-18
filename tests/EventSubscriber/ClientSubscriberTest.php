<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\EventSubscriber;

use Fluxter\SaasProviderBundle\EventSubscriber\ClientSubscriber;
use Fluxter\SaasProviderBundle\Model\Exception\ClientCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ClientSubscriberTest extends TestCase
{
    private Environment $twig;
    private DynamicSaasClientAccessorService $accessor;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader());
        $this->accessor = new DynamicSaasClientAccessorService();
    }

    public function testSubscribesToKernelRequest(): void
    {
        $this->assertSame([KernelEvents::REQUEST => ['checkSaasClient', 0]], ClientSubscriber::getSubscribedEvents());
    }

    public function testAddsTwigGlobalsWhenTenantIsFound(): void
    {
        $tenant = new Tenant('tenant-a.example.com');

        $this->subscriber($tenant)->checkSaasClient($this->event('tenant_show'));

        $globals = $this->twig->getGlobals();
        $this->assertSame($tenant, $globals['tenant_object']);
        $this->assertSame($this->accessor, $globals['tenant']);
    }

    public function testThrowsNotFoundWhenNoTenantIsFound(): void
    {
        $this->expectException(ClientCouldNotBeDiscoveredException::class);

        $this->subscriber(null, ['^public_'])->checkSaasClient($this->event('tenant_show'));
    }

    public function testThrowsNotFoundWithoutRoute(): void
    {
        $this->expectException(ClientCouldNotBeDiscoveredException::class);

        $this->subscriber(null, ['^public_'])->checkSaasClient($this->event(null));
    }

    public function testExcludedRoutesWorkWithoutTenant(): void
    {
        $subscriber = $this->subscriber(null, ['^admin_', '^public_']);
        $subscriber->checkSaasClient($this->event('public_health'));

        $this->assertArrayNotHasKey('tenant_object', $this->twig->getGlobals());
    }

    public function testIgnoresSubRequests(): void
    {
        $tenantService = $this->createMock(TenantService::class);
        $tenantService->expects($this->never())->method('getTenant');

        $subscriber = new ClientSubscriber($tenantService, $this->twig, $this->accessor);
        $subscriber->checkSaasClient($this->event('tenant_show', HttpKernelInterface::SUB_REQUEST));

        $this->assertArrayNotHasKey('tenant_object', $this->twig->getGlobals());
    }

    private function subscriber(?Tenant $tenant, array $excludeRoutes = []): ClientSubscriber
    {
        $tenantService = $this->createStub(TenantService::class);
        $tenantService->method('getTenant')->willReturn($tenant);

        return new ClientSubscriber($tenantService, $this->twig, $this->accessor, null, $excludeRoutes);
    }

    private function event(?string $route, int $requestType = HttpKernelInterface::MAIN_REQUEST): RequestEvent
    {
        $request = Request::create('http://tenant-a.example.com/');
        if (null !== $route) {
            $request->attributes->set('_route', $route);
        }

        return new RequestEvent($this->createStub(HttpKernelInterface::class), $request, $requestType);
    }
}
