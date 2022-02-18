<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

abstract class AbstractTenantRepository extends ServiceEntityRepository
{
    protected TenantService $clientService;

    public function findAll(): array
    {
        return $this->createQueryBuilder('e')->getQuery()->getResult();
    }

    /** @required */
    public function setTenantService(TenantService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $tenant = null): ?QueryBuilder
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
