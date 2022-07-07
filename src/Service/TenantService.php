<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TenantService
{
    private string $saasClientEntity;

    private ?TenantInterface $tenant = null;

    public function __construct(
        ContainerInterface $container, 
        private EntityManagerInterface $em, 
        private RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->saasClientEntity = $container->getParameter('saas_provider.client_entity');
        $this->requestStack = $requestStack;
    }

    public function createClient(array $parameters)
    {
        /** @var TenantInterface $client */
        $client = new $this->saasClientEntity();

        /** @var TenantChildInterface $parameter */
        foreach ($parameters as $parameter) {
            $client->addParameter($parameter);
        }

        $this->em->persist($client);
        $this->em->flush($client);

        return $client;
    }

    /**
     * Returns the current client, recognized by the url and the client entity.
     */
    public function getTenant(): ?TenantInterface
    {
        return $this->tenant ?? $this->discoverClient();
    }

    /**
     * Returns the current http host in lower case.
     * For example: "http://localhost:8000" returns "localhost"
     * https://test.test.de would return "test.test.de".
     */
    public function getCurrentHttpHost(): string
    {
        $url = $this->requestStack->getCurrentRequest()->getHttpHost();
        $url = strtolower($url);
        if (false !== strpos($url, ':')) {
            $url = preg_replace('/:(.*)/', '', $url);
        }

        return strtolower($url);
    }

    private function discoverClient(): ?TenantInterface
    {
        $url = $this->getCurrentHttpHost();
        $repo = $this->em->getRepository($this->saasClientEntity);
        $client = $repo->findOneBy(['url' => $url]);
        if (null == $client) {
            return null;
        }

        return $client;
    }

    /**
     * Set the value of tenant
     *
     * @return  self
     */ 
    public function setTenant($tenant)
    {
        $this->tenant = $tenant;

        return $this;
    }
}
