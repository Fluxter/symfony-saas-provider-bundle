<?php

namespace Fluxter\SaasProviderBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

class CreateClientType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('parameters', EntityType::class, [
            'entry_type' => ParameterType::class,
            'multiple' => true,
            'allow_add' => true
        ]);
    }
}
