<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TenantChildType extends EntityType
{
    public function __construct(ManagerRegistry $managerRegistry, private TenantService $clientService)
    {
        parent::__construct($managerRegistry);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}
