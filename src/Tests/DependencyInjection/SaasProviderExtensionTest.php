<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\DependencyInjection;

use Fluxter\SaasProviderBundle\DependencyInjection\SaasProviderExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class SaasProviderExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    protected $configuration;

    protected function tearDown(): void
    {
        $this->configuration = null;
    }

    public function testUserLoadUnlessClientEntitySet()
    {
        $loader = new SaasProviderExtension();
        $config = $this->getEmptyConfig();
        unset($config['client_entity']);

        $this->expectException(InvalidConfigurationException::class);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * getEmptyConfig.
     *
     * @return array
     */
    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
client_entity: Acme\MyBundle\Entity\User
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
