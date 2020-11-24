<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Service\SaasClientService;

trait SaasClientControllerTrait
{
    /** @var SaasClientService */
    private $clientService;

    /** @required */
    public function setClientService(SaasClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    protected function getCurrentClient(): ?SaasClientInterface
    {
        if ($this->clientService == null) {
            throw new \Exception("Clientservice was null, did you forget to autowire the Controller " . get_class($this) . "?");
        }
        return $this->clientService->getCurrentClient();
    }
}
