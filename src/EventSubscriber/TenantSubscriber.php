<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Fluxter\SaasProviderBundle\Model\Exception\TenantCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment as TwigEnvironment;

class TenantSubscriber implements EventSubscriberInterface
{
    private array $excludeRoutes = [];
    private ?string $globalUrl = null;

    public function __construct(
        private readonly TenantService $clientService,
        private readonly TwigEnvironment $twig,
        private readonly DynamicSaasClientAccessorService $dynamicSaasClientAccessorService,
        private readonly LoggerInterface $logger,
        ParameterBagInterface $paramBag)
    {
        if ($paramBag->has('saas_provider.exclude_routes')) {
            $this->excludeRoutes = $paramBag->get('saas_provider.exclude_routes');
        }
        if ($paramBag->has('saas_provider.global_url')) {
            $this->globalUrl = $paramBag->get('saas_provider.global_url');
        }
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
     *
     * @return void
     */
    public function checkTenant(KernelEvent $event)
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
            if ($event->getRequest()->getHttpHost() == $this->globalUrl) {
                $this->logger->debug("global url hit!");
                return;
            }
            
            $route = $event->getRequest()->get('_route');
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
