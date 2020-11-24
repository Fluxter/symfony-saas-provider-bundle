<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle;

use Fluxter\SaasProviderBundle\DependencyInjection\SaasProviderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SaasProviderBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SaasProviderExtension();
    }
}
