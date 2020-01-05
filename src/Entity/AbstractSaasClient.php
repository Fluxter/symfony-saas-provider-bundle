<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Entity;

use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Model\SaasParameterInterface;

abstract class AbstractSaasClient implements SaasClientInterface
{
    public function getSaasParameters(): array
    {
        // Todo
        return array();
    }

    public function addParamter(SaasParameterInterface $param)
    {
        // Todo
    }

    public function removeParameter(SaasParameterInterface $parameter)
    {
        // Todo
    }
}
