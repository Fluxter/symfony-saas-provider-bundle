<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Contracts\Service\Attribute\Required;

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

    #[Required]
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
            ...[$this->getTenantRelationPropertyName() => $tenant],
        ], $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null, ?TenantInterface $tenant = null): ?object
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        return parent::findOneBy([
            ...$criteria,
            ...[$this->getTenantRelationPropertyName() => $tenant],
        ], $orderBy);
    }

    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $tenant = null): ?QueryBuilder
    {
        if (null == $tenant) {
            $tenant = $this->clientService->getTenant();
        }

        $paramName = 'saasTenantId';

        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere(sprintf($alias . '.%s = :%s', $this->getTenantRelationPropertyName(), $paramName))
            ->setParameter($paramName, $tenant->getId());
    }

    // THIS IS ONLY FOR CONSOLE COMMANDS OR SOMETHING!
    public function createGlobalQueryBuilder($alias, $indexBy = null)
    {
        return parent::createQueryBuilder($alias, $indexBy);
    }

    protected function getTenantRelationPropertyName(): string
    {
        return 'tenant';
    }
}
