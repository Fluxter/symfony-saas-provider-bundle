<?php

namespace Fluxter\SaasProviderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Entity\Community;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;

class CommunityService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    private const SaasClientSessionIndex = "SAASCLIENT";

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    public function __call($name, $arguments)
    {
        $client = $this->getCurrentCommunity();

        $method = "get$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }
        $method = "is$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }

        throw new \Exception("Unknown Client function / variable: {$name}!");
    }

    public function getCurrent()
    {
        if (!$this->session->has(self::SaasClientSessionIndex)) {
            throw new \Exception("SAAS-CLIENT SESSION VARIABLE NOT SPECIFIED");
        }

        // Todo Entity name from configuration
        $repo = $this->em->getRepository(Community::class);

        /** @var SaasClientInterface $client */
        $client = $repo->findOneById($this->session->get(self::SaasClientSessionIndex));
        if ($client == null) {
            throw new \Exception("SAAS-CLIENT NOT FOUND!");
        }

        return $client;
    }
}
