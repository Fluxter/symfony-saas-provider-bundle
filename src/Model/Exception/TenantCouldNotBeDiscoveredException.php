<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
