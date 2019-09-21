<?php

namespace Fluxter\SaasProviderBundle\Controller\Abstraction;

use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class SaasClientController extends Controller
{
    use SaasClientControllerTrait;
}
