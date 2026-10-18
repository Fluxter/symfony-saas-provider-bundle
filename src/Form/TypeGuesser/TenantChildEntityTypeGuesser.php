<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Form\TypeGuesser;

use Fluxter\SaasProviderBundle\Form\Type\TenantChildType;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class TenantChildEntityTypeGuesser extends DoctrineOrmTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        if (!$ret = $this->getMetadata($class)) {
            return null;
        }

        [$metadata, $name] = $ret;
        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $class = $metadata->getAssociationTargetClass($property);

            if (is_subclass_of($class, TenantChildInterface::class)) {
                return new TypeGuess(TenantChildType::class, [
                    'em' => $name,
                    'class' => $class,
                    'multiple' => $multiple,
                ], Guess::HIGH_CONFIDENCE);
            }
        }

        return null;
    }
}
