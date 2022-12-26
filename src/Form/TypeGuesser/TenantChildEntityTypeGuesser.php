<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Form\TypeGuesser;

use Fluxter\SaasProviderBundle\Form\Type\ClientEntityType;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class TenantChildEntityTypeGuesser extends DoctrineOrmTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType($class, $property): ?TypeGuess
    {
        if (!$ret = $this->getMetadata($class)) {
            return null;
        }

        list($metadata, $name) = $ret;
        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);

            $class = $mapping['targetEntity'];
            $interfaces = class_implements($class);
            if (in_array(TenantChildInterface::class, $interfaces)) {
                return new TypeGuess(ClientEntityType::class, [
                    'em' => $name,
                    'class' => $class,
                    'multiple' => $multiple,
                ], Guess::HIGH_CONFIDENCE);
            }
        }

        return null;
    }
}
