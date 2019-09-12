<?php

namespace Fluxter\SaasProviderBundle\Controller;

use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SaasClientController extends Controller
{
    /** @var SaasClientService */
    private $clientService;

    public function __construct(SaasClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function createClientAction()
    { }
}
