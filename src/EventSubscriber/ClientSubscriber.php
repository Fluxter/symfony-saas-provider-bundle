<?php

namespace Fluxter\SaasProviderBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\SaasClientUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Component\HttpFoundation\Response;

class ClientSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var SaasClientService */
    private $clientService;

    public function __construct(EntityManagerInterface $em, SessionInterface $session, SaasClientService $clientService)
    {
        $this->em = $em;
        $this->session = $session;
        $this->clientService = $clientService;
    }

    public static function getSubscribedEvents()
    {
        $events = [
            KernelEvents::REQUEST => ['checkSaasClient', 101],
        ];

        if (class_exists("FOS\UserBundle\FOSUserEvents")) {
            $events[\FOS\UserBundle\FOSUserEvents::REGISTRATION_SUCCESS] = 'addSaasClientAfterSuccessfullRegistration';
        }

        return $events;
    }

    /**
     * Add the correct client after successfull registration
     *
     * @param \FOS\UserBundle\Event\FormEvent $event
     * @return void
     */
    public function addSaasClientAfterSuccessfullRegistration($event)
    {
        /** @var SaasClientUserInterface $user */
        $user = $event->getForm()->getData();

        // Todo get the default role by the saasclient
        $defaultRole = null;
        $client = $this->clientService->getCurrentClient();

        $user->addRole($client, $defaultRole);
    }

    /**
     * Discovers and checks if the current saas client exists by the current domain
     * and sets the session variables and updates them if needed.
     *
     * @param KernelEvent $event
     * @return void
     */
    public function checkSaasClient(KernelEvent $event)
    {
        $current = $this->clientService->getCurrentClient();
        if ($current == null) {
            $event->setResponse(new Response('Not found!', 404));
            return;           
        }
    }
}
