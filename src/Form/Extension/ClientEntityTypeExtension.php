<?php

/*
 * This file is part of the ClanManager package.
 * (c) Fluxter <https://fluxter.net/>
 * Found us at <https://clanmanager.net>
 */

namespace Fluxter\SaasProviderBundle\Form\Extension;

use Fluxter\SaasProviderBundle\Form\Type\ClientEntityType;
use Symfony\Component\Form\AbstractTypeExtension;

class ClientEntityTypeExtension extends AbstractTypeExtension
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
    }

    public static function getExtendedTypes()
    {
        return [
            ClientEntityType::class
        ];
    }
}
