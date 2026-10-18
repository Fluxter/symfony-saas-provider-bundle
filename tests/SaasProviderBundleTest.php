<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests;

use Fluxter\SaasProviderBundle\DependencyInjection\SaasProviderExtension;
use Fluxter\SaasProviderBundle\SaasProviderBundle;
use PHPUnit\Framework\TestCase;

class SaasProviderBundleTest extends TestCase
{
    public function testContainerExtension(): void
    {
        $extension = (new SaasProviderBundle())->getContainerExtension();

        $this->assertInstanceOf(SaasProviderExtension::class, $extension);
        $this->assertSame('saas_provider', $extension->getAlias());
    }
}
