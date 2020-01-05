<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\Exception\ClientCouldNotBeDiscoveredException;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Model\SaasParameterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SaasClientService
{
    private const SaasClientSessionIndex = 'SAASCLIENT';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var string */
    private $saasClientEntity;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->session = $session;
        $this->saasClientEntity = $container->getParameter('saas_provider.client_entity');
        $this->requestStack = $requestStack;
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
        $client = new $this->saasClientEntity();

        /** @var SaasParameterInterface $parameter */
        foreach ($parameters as $parameter) {
            $client->addParameter($parameter);
        }

        $this->em->persist($client);
        $this->em->flush($client);

        return $client;
    }

    /**
     * Returns the current client, recognized by the url and the client entity.
     *
     * @return SaasClientInterface|null
     */
    public function getCurrentClient(bool $autodiscover = true): SaasClientInterface
    {
        if (!$this->session->has(self::SaasClientSessionIndex) || null == $this->session->get(self::SaasClientSessionIndex)) {
            if ($autodiscover) {
                $this->discoverClient();

                return $this->getCurrentClient(false);
            }

            return null;
        }

        $repo = $this->em->getRepository($this->saasClientEntity);
        /** @var SaasClientInterface $client */
        $client = $repo->findOneById($this->session->get(self::SaasClientSessionIndex));
        if (null == $client) {
            throw new ClientCouldNotBeDiscoveredException();
        }

        // Validate
        $url = $this->getCurrentHttpHost();
        if (strtolower($client->getUrl()) != strtolower($url)) {
            $this->resetSaasClientSession();
            $this->discoverClient();

            return $this->getCurrentClient(false);
        }

        if (null == $client) {
            throw new ClientCouldNotBeDiscoveredException();
        }

        return $client;
    }

    /**
     * Returns the current http host in lower case.
     * For example: "http://localhost:8000" returns "localhost"
     * https://test.test.de would return "test.test.de".
     */
    private function getCurrentHttpHost(): string
    {
        $url = $this->requestStack->getCurrentRequest()->getHttpHost();
        $url = strtolower($url);
        if (false !== strpos($url, ':')) {
            $url = preg_replace('/:(.*)/', '', $url);
        }

        return strtolower($url);
    }

    private function discoverClient(): ?SaasClientInterface
    {
        $url = $this->getCurrentHttpHost();
        $repo = $this->em->getRepository($this->saasClientEntity);
        $client = $repo->findOneBy(['url' => $url]);
        if (null == $client) {
            return null;
        }

        $this->saveSaasClientSession($client);

        return $client;
    }

    private function resetSaasClientSession()
    {
        $this->session->set(self::SaasClientSessionIndex, null);
    }

    private function saveSaasClientSession(SaasClientInterface $client)
    {
        $this->session->set(self::SaasClientSessionIndex, $client->getId());
    }
}
