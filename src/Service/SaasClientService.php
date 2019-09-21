<?php

namespace Fluxter\SaasProviderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Model\SaasParameterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SaasClientService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    private $clientEntity;

    private const SaasClientSessionIndex = "SAASCLIENT";

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
        $this->clientEntity = $container->getParameter('fluxter.saasprovider.cliententity');
    }

    public function __call($name, $arguments)
    {
        $client = $this->getCurrentClient();

        $method = "get$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }
        $method = "is$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }

        throw new \Exception("Unknown SaaS-Client function / variable: {$name}!");
    }

    public function createClient(array $parameters)
    {
        /** @var SaasClientInterface $client */
        $client = new $this->clientEntity;

        /** @var SaasParameterInterface $parameter */
        foreach ($parameters as $parameter) {
            $client->addParameter($parameter);
        }

        $this->em->persist($client);
        $this->em->flush($client);

        return $client;
    }

    public function getCurrentClient()
    {
        if (!$this->session->has(self::SaasClientSessionIndex)) {
            throw new \Exception("SAAS-CLIENT SESSION VARIABLE NOT SPECIFIED");
        }

        // Todo Entity name from configuration
        $repo = $this->em->getRepository($this->clientEntity);

        /** @var SaasClientInterface $client */
        $client = $repo->findOneById($this->session->get(self::SaasClientSessionIndex));
        if ($client == null) {
            throw new \Exception("SAAS-CLIENT NOT FOUND!");
        }

        return $client;
    }
}
