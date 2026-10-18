<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Fluxter\SaasProviderBundle\Model\Exception\ClientCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class ClientSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    /**
     * @param string[] $excludeRoutes regular expressions (without delimiters) of routes that work without a tenant
     */
    public function __construct(
        private TenantService $clientService,
        private TwigEnvironment $twig,
        private DynamicSaasClientAccessorService $dynamicSaasClientAccessorService,
        ?LoggerInterface $logger = null,
        private array $excludeRoutes = [],
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        $events = [
            KernelEvents::REQUEST => ['checkSaasClient', 0],
        ];

        return $events;
    }

    /**
     * Discovers and checks if the current saas client exists by the current domain
     * and sets the session variables and updates them if needed.
     */
    public function checkSaasClient(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $client = $this->clientService->getTenant();

        // This is not important, because an error is thrown on "getTenant"
        if ($client) {
            $this->twig->addGlobal('tenant_object', $client);
            $this->twig->addGlobal('tenant', $this->dynamicSaasClientAccessorService);
        } else {
            $route = (string) $event->getRequest()->attributes->get('_route');
            foreach ($this->excludeRoutes as $pattern) {
                if (preg_match("/{$pattern}/", $route)) {
                    $this->logger->debug("Found $route in saas_provider.exclude_routes");

                    return;
                }
                $this->logger->debug("$route didn´t match pattern $pattern");
            }

            throw new ClientCouldNotBeDiscoveredException();
        }
    }
}
