<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class ClientSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $globalUrl = null;

    public function __construct(
        private TenantService $clientService,
        private TwigEnvironment $twig,
        private DynamicSaasClientAccessorService $dynamicSaasClientAccessorService,
        ContainerInterface $container)
    {
        $this->clientService = $clientService;
        $this->twig = $twig;
        $this->dynamicSaasClientAccessorService = $dynamicSaasClientAccessorService;
        if ($container->hasParameter('saas_provider.global_url')) {
            $this->globalUrl = $container->getParameter('saas_provider.global_url');
        }
    }

    public static function getSubscribedEvents()
    {
        $events = [
            KernelEvents::REQUEST => ['checkSaasClient', 101],
        ];

        return $events;
    }

    /**
     * Discovers and checks if the current saas client exists by the current domain
     * and sets the session variables and updates them if needed.
     *
     * @return void
     */
    public function checkSaasClient(KernelEvent $event)
    {
        $client = $this->clientService->getTenant();
        if (null != $this->globalUrl && $this->globalUrl == $this->clientService->getCurrentHttpHost()) {
            // We dont do anything because this is a "not client" page or something ;)
            return;
        }

        // This is not important, because an error is thrown on "getTenant"
        if (null == $client) {
            $event->setResponse(new Response('Not found!', 404));

            return;
        }

        $this->twig->addGlobal('tenant_object', $client);
        $this->twig->addGlobal('tenant', $this->dynamicSaasClientAccessorService);
    }
}
