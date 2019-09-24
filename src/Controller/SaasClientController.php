<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Controller;

use Fluxter\SaasProviderBundle\Form\CreateClientType;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SaasClientController extends Controller
{
    /** @var SaasClientService */
    private $clientService;

    private $clientEntity;

    public function __construct(SaasClientService $clientService, ContainerInterface $container)
    {
        $this->clientService = $clientService;
        $this->clientEntity = $container->getParameter('fluxter.saasprovider.cliententity');
    }

    public function createClientAction(Request $request, string $apikey)
    {
        // Todo check the apikey

        /** @var SaasClientInterface $client */
        $client = new $this->clientEntity();
        $form = $this->createForm(CreateClientType::class, $client);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json($form, 400);
        }

        $client = $this->clientService->createClient($form->get('parameters')->getData());

        return $this->json(array('success' => true));
    }
}
