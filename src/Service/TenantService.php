<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\Exception\TenantCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class TenantService
{
    private string $saasClientEntity;

    private ?TenantInterface $tenant = null;
    
    private ?RequestContext $originalRouterContext = null;

    public function __construct(
        ParameterBagInterface $paramBag,
        private EntityManagerInterface $em, 
        private RequestStack $requestStack,
        private readonly RouterInterface $router,
        private readonly KernelInterface $kernel,
    ) {
        $this->em = $em;
        $this->saasClientEntity = $paramBag->get('saas_provider.client_entity');
        $this->requestStack = $requestStack;
    }


    public function resetRouterContext()
    {
        if (!$this->originalRouterContext) {
            return;
        }
        $this->router->setContext($this->originalRouterContext);
        $this->originalRouterContext = null;
    }
    
    public function getTenantRouter(): RouterInterface
    {
        $router = clone $this->router;
        $context = $router->getContext();
        $tenant = $this->getTenant();
        if (!$tenant) {
            throw new TenantCouldNotBeDiscoveredException();
        }
        $context->setHost($this->getTenant()->getUrl());

        if ('dev' == $this->kernel->getEnvironment()) {
            $context
                ->setScheme('http')
            ;
        } else {
            $context->setScheme('https');
        }

        return $router;
    }

    public function createClient(array $parameters): TenantInterface
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
    public function getCurrentHttpHost(): ?string
    {
        $url = $this->requestStack->getCurrentRequest()?->getHttpHost();
        if (!$url) {
            return null;
        }

        $url = strtolower($url);
        if (false !== strpos($url, ':')) {
            $url = preg_replace('/:(.*)/', '', $url);
        }

        return strtolower($url);
    }

    private function discoverClient(): ?TenantInterface
    {
        $url = $this->getCurrentHttpHost();
        if (!$url) {
            return null;
        }

        $repo = $this->em->getRepository($this->saasClientEntity);
        $client = $repo->findOneBy(['url' => $url]);
        if (null == $client) {
            return null;
        }

        $this->tenant = $client;

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
