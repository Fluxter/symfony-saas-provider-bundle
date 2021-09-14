<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;

trait TenantClientControllerTrait
{
    /** @var TenantService */
    private $clientService;

    /** @required */
    public function setClientService(TenantService $clientService)
    {
        $this->clientService = $clientService;
    }

    protected function getTenant(): ?TenantInterface
    {
        if ($this->clientService == null) {
            throw new \Exception("Clientservice was null, did you forget to autowire the Controller " . get_class($this) . "?");
        }
        return $this->clientService->getTenant();
    }
}
