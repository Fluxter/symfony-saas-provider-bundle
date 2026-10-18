<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Entity;

use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Model\TenantInterface;

abstract class AbstractSaasClient implements TenantInterface
{
    public function getSaasParameters(): array
    {
        // Todo
        return [];
    }

    public function addParameter(TenantChildInterface $parameter)
    {
        // Todo
    }

    /**
     * @deprecated misspelled, use addParameter() instead
     */
    public function addParamter(TenantChildInterface $param)
    {
        return $this->addParameter($param);
    }

    public function removeParameter(TenantChildInterface $parameter)
    {
        // Todo
    }
}
