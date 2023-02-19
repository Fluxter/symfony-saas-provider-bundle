<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

class TenantChildEntitySubscriber implements EventSubscriberInterface
{
    public function __construct(private TenantService $tenantService)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist => 'prePersist',
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
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
