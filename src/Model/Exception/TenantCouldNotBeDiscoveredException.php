<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Model\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantCouldNotBeDiscoveredException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('The client was not provided and could not be determinded by the request!');
    }
}
