<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

abstract class AbstractClientRelatedRepository extends ServiceEntityRepository
{
    /** @var TenantService */
    protected $clientService;

    /** @required */
    public function setTenantService(TenantService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $client = null)
    {
        if (null == $client) {
            $client = $this->clientService->getTenant();
        }

        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere($alias . '.client = :saasClientId')
            ->setParameter('saasClientId', $client->getId());
    }
}
