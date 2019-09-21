<?php

namespace Fluxter\SaasProviderBundle\Model;

interface SaasClientInterface
{
    function getId();

    function getSaasParameters(): array;

    function addParameter(SaasParameterInterface $parameter);

    function removeParameter(SaasParameterInterface $parameter);
}
