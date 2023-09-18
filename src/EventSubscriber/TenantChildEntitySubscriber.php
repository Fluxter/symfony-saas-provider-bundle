<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

#[AsDoctrineListener(Events::prePersist)]
class TenantChildEntitySubscriber
{
    public function __construct(private readonly TenantService $tenantService)
    {
    }

    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof TenantChildInterface) {
            return;
        }

        $tenant = $this->tenantService->getTenant();
        if (!$tenant) {
            return;
        }

        $entity->setTenant($tenant);
    }
}
