<?php

namespace Fluxter\SaasProviderBundle\DependencyInjection;

use Fluxter\SaasProviderBundle\Model\SaasClientInterface;

trait SaasControllerTrait
{
    protected function getClient(): SaasClientInterface
    { }
}
