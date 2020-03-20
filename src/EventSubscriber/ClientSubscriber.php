<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\SaasClientUserInterface;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment as TwigEnvironment;

class ClientSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var SaasClientService */
    private $clientService;

    /** @var TwigEnvironment */
    private $twig;

    /** @var DynamicSaasClientAccessorService */
    private $dynamicSaasClientAccessorService;

    public function __construct(
        EntityManagerInterface $em, 
        SessionInterface $session, 
        SaasClientService $clientService, 
        TwigEnvironment $twig,
        DynamicSaasClientAccessorService $dynamicSaasClientAccessorService)
    {
        $this->em = $em;
        $this->session = $session;
        $this->clientService = $clientService;
        $this->twig = $twig;
        $this->dynamicSaasClientAccessorService = $dynamicSaasClientAccessorService;
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
        $client = $this->clientService->tryGetCurrentClient();
        if (null == $client) {
            $event->setResponse(new Response('Not found!', 404));

            return;
        }

        $this->twig->addGlobal('saas_client_object', $client);
        $this->twig->addGlobal('saas_client', $this->dynamicSaasClientAccessorService);
    }
}
