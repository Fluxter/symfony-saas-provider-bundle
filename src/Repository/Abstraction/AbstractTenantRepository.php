<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

/**
 * @template T
 * @extends ServiceEntityRepository<T>
 */
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

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null, ?TenantInterface $tenant = null): array
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        return parent::findBy([
            ...$criteria,
            ...['tenant' => $tenant],
        ], $orderBy, $limit, $offset);
    }


    public function findOneBy(array $criteria, ?array $orderBy = null, ?TenantInterface $tenant = null): ?object
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        return parent::findOneBy([
            ...$criteria,
            ...['tenant' => $tenant],
        ], $orderBy);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }

    protected function getTenantRelationPropertyName(): string
    {
        return "tenant";
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $tenant = null): ?QueryBuilder
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        $paramName = "saasTenantId";
        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere(sprintf($alias . '.%s = :%s', $this->getTenantRelationPropertyName(), $paramName))
            ->setParameter($paramName, $tenant->getId());
    }

// THIS IS ONLY FOR CONSOLE COMMANDS OR SOMETHING!
    public function createGlobalQueryBuilder($alias, $indexBy = null)
    {
        return parent::createQueryBuilder($alias, $indexBy);
    }
}
