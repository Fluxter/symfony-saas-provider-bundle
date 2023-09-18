<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Form\Extension;

use Fluxter\SaasProviderBundle\Form\Type\ClientEntityType;
use Symfony\Component\Form\AbstractTypeExtension;

class ClientEntityTypeExtension extends AbstractTypeExtension
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }

    public static function getExtendedTypes(): iterable
    {
        return [
            ClientEntityType::class,
        ];
    }
}
