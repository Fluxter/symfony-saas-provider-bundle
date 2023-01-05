<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Service;

class DynamicSaasClientAccessorService
{
    /** @var TenantService */
    private $_clientService;

    public function __call($name, $arguments)
    {
        $client = $this->_clientService->getTenant();

        $method = $name;
        if (method_exists($client, $method)) {
            return $client->$method();
        }

        $method = "get$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }
        $method = "is$name";
        if (method_exists($client, $method)) {
            return $client->$method();
        }

        throw new \Exception("Unknown Settings function / variable: {$name}!");
    }

    /** @required */
    public function setClientService(TenantService $clientService)
    {
        $this->_clientService = $clientService;
    }
}
