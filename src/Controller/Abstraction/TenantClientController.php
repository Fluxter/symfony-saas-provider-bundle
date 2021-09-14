<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class TenantClientController extends AbstractController
{
    use TenantClientControllerTrait;
}
