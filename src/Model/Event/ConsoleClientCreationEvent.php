<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Model\Event;

use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\Event;

class ConsoleClientCreationEvent extends Event
{
    private TenantInterface $tenant;
    private SymfonyStyle $ss;

    public function __construct(TenantInterface $tenant, SymfonyStyle $ss)
    {
        $this->tenant = $tenant;
        $this->ss = $ss;
    }

    /**
     * Get the value of tenant.
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * Set the value of tenant.
     *
     * @return self
     */
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Get the value of ss.
     */
    public function console()
    {
        return $this->ss;
    }
}
