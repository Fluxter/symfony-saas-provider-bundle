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
use Symfony\Contracts\Service\Attribute\Required;

trait TenantClientControllerTrait
{
    private ?TenantService $clientService = null;

    #[Required]
    public function setClientService(TenantService $clientService): void
    {
        $this->clientService = $clientService;
    }

    protected function getTenant(): ?TenantInterface
    {
        if (null === $this->clientService) {
            throw new \LogicException('Clientservice was null, did you forget to autowire the Controller ' . static::class . '?');
        }

        return $this->clientService->getTenant();
    }
}
