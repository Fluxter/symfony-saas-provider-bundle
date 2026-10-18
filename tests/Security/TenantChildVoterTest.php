<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Security;

use Fluxter\SaasProviderBundle\Security\TenantChildVoter;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TenantChildVoterTest extends TestCase
{
    private Tenant $tenantA;
    private Tenant $tenantB;

    protected function setUp(): void
    {
        $this->tenantA = (new Tenant('tenant-a.example.com'))->setId(1);
        $this->tenantB = (new Tenant('tenant-b.example.com'))->setId(2);
    }

    public function testAbstainsForOtherSubjects(): void
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->vote($this->tenantA, new \stdClass()));
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->vote($this->tenantA, null));
    }

    public function testDeniesWithoutCurrentTenant(): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->vote(null, (new Note())->setTenant($this->tenantA)));
    }

    public function testGrantsChildrenWithoutTenant(): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->vote($this->tenantA, new Note()));
    }

    public function testGrantsChildrenOfCurrentTenant(): void
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->vote($this->tenantA, (new Note())->setTenant($this->tenantA)));
    }

    public function testDeniesChildrenOfOtherTenants(): void
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->vote($this->tenantA, (new Note())->setTenant($this->tenantB)));
    }

    private function vote(?Tenant $currentTenant, mixed $subject): int
    {
        $tenantService = $this->createStub(TenantService::class);
        $tenantService->method('getTenant')->willReturn($currentTenant);

        return (new TenantChildVoter($tenantService))->vote($this->createStub(TokenInterface::class), $subject, ['EDIT']);
    }
}
