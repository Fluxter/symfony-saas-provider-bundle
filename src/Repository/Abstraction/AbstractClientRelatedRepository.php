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
use Fluxter\SaasProviderBundle\Service\SaasClientService;

abstract class AbstractClientRelatedRepository extends ServiceEntityRepository
{
    /** @var SaasClientService */
    protected $clientService;

    /** @required */
    public function setSaasClientService(SaasClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function createQueryBuilder($alias, $indexBy = null, ?TenantInterface $client = null)
    {
        if (null == $client) {
            $client = $this->clientService->getCurrentClient();
        }

        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere($alias . '.client = :saasClientId')
            ->setParameter('saasClientId', $client->getId());
    }
}
