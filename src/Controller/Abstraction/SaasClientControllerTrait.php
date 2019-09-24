<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Fluxter\SaasProviderBundle\Service\SaasClientService;

trait SaasClientControllerTrait
{
    /** @var SaasClientService */
    private $clientService;

    /**
     * Set the value of clientService.
     *
     * @return self
     */
    public function setClientService(SaasClientService $clientService)
    {
        $this->clientService = $clientService;

        return $this;
    }

    protected function getCurrentClient()
    {
        return $this->clientService->getCurrentClient();
    }
}
