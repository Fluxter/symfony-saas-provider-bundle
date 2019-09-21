<?php

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

trait SaasClientControllerTrait
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
