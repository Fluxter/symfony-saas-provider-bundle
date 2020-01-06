<?php

/*
 * This file is part of the ClanManager package.
 * (c) Fluxter <https://fluxter.net/>
 * Found us at <https://clanmanager.net>
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
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

    public function createQueryBuilder($alias, $indexBy = null, ?SaasClientInterface $client = null)
    {
        if (null == $client) {
            $client = $this->clientService->getCurrentClient();
        }

        return parent::createQueryBuilder($alias, $indexBy)
            ->andWhere($alias . '.client = :saasClientId')
            ->setParameter('saasClientId', $client->getId());
    }
}
