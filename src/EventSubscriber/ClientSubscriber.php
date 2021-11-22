<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Fluxter\SaasProviderBundle\Model\Exception\ClientCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class ClientSubscriber implements EventSubscriberInterface
{
    private array $excludeRoutes = [];

    public function __construct(
        private TenantService $clientService,
        private TwigEnvironment $twig,
        private DynamicSaasClientAccessorService $dynamicSaasClientAccessorService,
        private LoggerInterface $logger,
        ContainerInterface $container)
    {
        $this->clientService = $clientService;
        $this->twig = $twig;
        $this->dynamicSaasClientAccessorService = $dynamicSaasClientAccessorService;

        if ($container->hasParameter('saas_provider.exclude_routes')) {
            $this->excludeRoutes = $container->getParameter('saas_provider.exclude_routes');
        }
    }

    public static function getSubscribedEvents()
    {
        $events = [
            KernelEvents::REQUEST => ['checkSaasClient', 0],
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

        // This is not important, because an error is thrown on "getTenant"
        if ($client) {
            $this->twig->addGlobal('tenant_object', $client);
            $this->twig->addGlobal('tenant', $this->dynamicSaasClientAccessorService);
        } else {
            $route = $event->getRequest()->get('_route');
            foreach ($this->excludeRoutes as $pattern) {
                if (preg_match("/${pattern}/", $route)) {
                    $this->logger->debug("Found $route in saas_provider.exclude_routes");

                    return;
                } else {
                    
                    $this->logger->debug("$route didn´t match pattern $pattern");
                }
            }

            throw new ClientCouldNotBeDiscoveredException();
        }
    }
}
