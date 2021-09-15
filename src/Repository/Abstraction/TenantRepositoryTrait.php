<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

trait TenantRepositoryTrait
{
    protected TenantService $clientService;

    /** @required */
    public function setTenantService(TenantService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $tenant = null)
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere($alias . '.tenant = :saasTenantId')
            ->setParameter('saasTenantId', $tenant->getId());
    }

    // THIS IS ONLY FOR CONSOLE COMMANDS OR SOMETHING!
    public function createGlobalQueryBuilder($alias, $indexBy = null)
    {
        return parent::createGlobalQueryBuilder($alias, $indexBy);
    }
}
