<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Repository\Abstraction;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;

trait ClientRepositoryTrait
{
    public function createClientQueryBuilder(SaasClientInterface $client, string $alias, $indexedBy = null)
    {
        /** @var ServiceEntityRepository $this */
        $qb = $this->createQueryBuilder($alias, $indexedBy);
        $qb
            ->andWhere($alias . '.client = :client')
            ->setParameter('client', $client->getId());

        return $qb;
    }
}
