<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientEntityType extends EntityType
{
    /** @var SaasClientService */
    private $clientService;

    public function __construct(ManagerRegistry $managerRegistry, SaasClientService $clientService)
    {
        parent::__construct($managerRegistry);
        $this->clientService = $clientService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}