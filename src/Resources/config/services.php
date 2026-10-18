<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Fluxter\SaasProviderBundle\Command\CreateClientCommand;
use Fluxter\SaasProviderBundle\EventSubscriber\TenantChildEntitySubscriber;
use Fluxter\SaasProviderBundle\EventSubscriber\TenantSubscriber;
use Fluxter\SaasProviderBundle\Form\Extension\ClientEntityTypeExtension;
use Fluxter\SaasProviderBundle\Form\Type\TenantChildType;
use Fluxter\SaasProviderBundle\Form\TypeGuesser\TenantChildEntityTypeGuesser;
use Fluxter\SaasProviderBundle\Security\TenantChildVoter;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire();

    // Commands
    $services->set(CreateClientCommand::class)
        ->autoconfigure()
        ->arg('$saasClientEntity', param('saas_provider.client_entity'));

    // System services
    $services->set(TenantService::class)
        ->public()
        ->arg('$saasClientEntity', param('saas_provider.client_entity'))
        ->tag('kernel.reset', ['method' => 'reset']);
    $services->set(DynamicSaasClientAccessorService::class)
        ->public();

    // Form types, extensions and guessers
    $services->set(TenantChildType::class)
        ->tag('form.type');
    $services->set(ClientEntityTypeExtension::class)
        ->tag('form.type_extension');
    $services->set(TenantChildEntityTypeGuesser::class)
        ->tag('form.type_guesser');

    // Voter
    $services->set(TenantChildVoter::class)
        ->tag('security.voter');

    // Event listeners
    $services->set(TenantSubscriber::class)
        ->arg('$excludeRoutes', param('saas_provider.exclude_routes'))
        ->arg('$globalUrl', param('saas_provider.global_url'))
        ->tag('kernel.event_subscriber');
    $services->set(TenantChildEntitySubscriber::class)
        ->tag('doctrine.event_listener', ['event' => Events::prePersist]);
};
