<?php

namespace Fluxter\SaasProviderBundle\Entity;

use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Fluxter\SaasProviderBundle\Model\SaasParameterInterface;

abstract class AbstractSaasClient implements SaasClientInterface
{
    public function getSaasParameters() :array
    {
        // Todo
        return [];
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
