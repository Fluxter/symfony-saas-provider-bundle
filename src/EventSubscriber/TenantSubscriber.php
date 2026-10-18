<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Fluxter\SaasProviderBundle\Model\Exception\TenantCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class TenantSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    /**
     * @param string[] $excludeRoutes regular expressions (without delimiters) of routes that work without a tenant
     * @param ?string  $globalUrl     host that works without a tenant
     */
    public function __construct(
        private readonly TenantService $clientService,
        private readonly TwigEnvironment $twig,
        private readonly DynamicSaasClientAccessorService $dynamicSaasClientAccessorService,
        ?LoggerInterface $logger = null,
        private array $excludeRoutes = [],
        private ?string $globalUrl = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['checkTenant', 0],
        ];
    }

    /**
     * Discovers and checks if the current saas client exists by the current domain
     * and sets the session variables and updates them if needed.
     */
    public function checkTenant(RequestEvent $event): void
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
            if (null !== $this->globalUrl && $event->getRequest()->getHttpHost() == $this->globalUrl) {
                $this->logger->debug('global url hit!');

                return;
            }

            $route = (string) $event->getRequest()->attributes->get('_route');
            foreach ($this->excludeRoutes as $pattern) {
                if (preg_match("/{$pattern}/", $route)) {
                    $this->logger->debug("Found $route in saas_provider.exclude_routes");

                    return;
                }
                $this->logger->debug("$route didn´t match pattern $pattern");
            }

            throw new TenantCouldNotBeDiscoveredException();
        }
    }
}
