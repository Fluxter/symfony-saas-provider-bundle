<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Entity;

use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;

abstract class AbstractSaasClient implements TenantInterface
{
    public function getSaasParameters(): array
    {
        // Todo
        return [];
    }

    public function addParamter(TenantChildInterface $param)
    {
        // Todo
    }

    public function removeParameter(TenantChildInterface $parameter)
    {
        // Todo
    }
}
