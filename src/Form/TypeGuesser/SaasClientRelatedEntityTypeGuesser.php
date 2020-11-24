<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Form\TypeGuesser;

use Fluxter\SaasProviderBundle\Form\Type\ClientEntityType;
use Fluxter\SaasProviderBundle\Model\SaasClientRelatedInterface;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class SaasClientRelatedEntityTypeGuesser extends DoctrineOrmTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType($class, $property)
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
            if (in_array(SaasClientRelatedInterface::class, $interfaces)) {
                return new TypeGuess(ClientEntityType::class, [
                    'em' => $name,
                    'class' => $class,
                    'multiple' => $multiple,
                ], Guess::HIGH_CONFIDENCE);
            }
        }
    }
}
