<?php 

namespace Fluxter\SaasProviderBundle\Form\TypeGuesser;

use Fluxter\SaasProviderBundle\Form\Type\ClientEntityType;
use Fluxter\SaasProviderBundle\Model\SaasClientRelatedEntityInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class SaasClientRelatedEntityTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType($class, $property)
    {
        $interfaces = class_implements($class);
        if (in_array(SaasClientRelatedEntityInterface::class, $interfaces)) {
            return new TypeGuess(ClientEntityType::class, [], Guess::HIGH_CONFIDENCE);
        }
    }

    public function guessRequired($class, $property)
    {
    }

    public function guessMaxLength($class, $property)
    {
    }

    public function guessPattern($class, $property)
    {
    }
}