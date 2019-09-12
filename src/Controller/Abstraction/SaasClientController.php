<?php

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class SaasClientController extends Controller
{
    /** @var SaasClientService */
    private $clientService;

    /**
     * Set the value of clientService
     *
     * @return  self
     */
    public function setClientService(SaasClientService $clientService)
    {
        $this->clientService = $clientService;

        return $this;
    }

    protected function getCurrentClient()
    {
        return $this->clientService->getCurrent();
    }
}
