<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Fluxter\SaasProviderBundle\EventSubscriber\TenantChildEntitySubscriber;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\TestCase;

class TenantChildEntitySubscriberTest extends TestCase
{
    public function testAssignsCurrentTenantToNewChildren(): void
    {
        $tenant = new Tenant('tenant-a.example.com');
        $note = new Note('note');

        $this->listener($tenant)->prePersist($this->args($note));

        $this->assertSame($tenant, $note->getTenant());
    }

    public function testOverwritesAssignedTenantWithCurrentTenant(): void
    {
        $current = new Tenant('tenant-a.example.com');
        $note = (new Note('note'))->setTenant(new Tenant('tenant-b.example.com'));

        $this->listener($current)->prePersist($this->args($note));

        $this->assertSame($current, $note->getTenant());
    }

    public function testKeepsAssignedTenantWithoutCurrentTenant(): void
    {
        $assigned = new Tenant('tenant-b.example.com');
        $note = (new Note('note'))->setTenant($assigned);

        $this->listener(null)->prePersist($this->args($note));

        $this->assertSame($assigned, $note->getTenant());
    }

    public function testDoesNothingWithoutCurrentTenant(): void
    {
        $note = new Note('note');

        $this->listener(null)->prePersist($this->args($note));

        $this->assertNull($note->getTenant());
    }

    public function testIgnoresOtherEntities(): void
    {
        $tenantService = $this->createMock(TenantService::class);
        $tenantService->expects($this->never())->method('getTenant');

        (new TenantChildEntitySubscriber($tenantService))->prePersist($this->args(new Tenant('tenant-a.example.com')));
    }

    private function listener(?Tenant $tenant): TenantChildEntitySubscriber
    {
        $tenantService = $this->createStub(TenantService::class);
        $tenantService->method('getTenant')->willReturn($tenant);

        return new TenantChildEntitySubscriber($tenantService);
    }

    private function args(object $entity): PrePersistEventArgs
    {
        return new PrePersistEventArgs($entity, $this->createStub(EntityManagerInterface::class));
    }
}
