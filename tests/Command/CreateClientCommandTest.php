<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Command\CreateClientCommand;
use Fluxter\SaasProviderBundle\Model\Event\ConsoleClientCreationEvent;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CreateClientCommandTest extends TestCase
{
    public function testNameAndDescription(): void
    {
        $command = new CreateClientCommand($this->createStub(EntityManagerInterface::class), new EventDispatcher(), $this->createStub(TenantService::class), Tenant::class);

        $this->assertSame('fluxter:saas:provider:create-client', $command->getName());
        $this->assertSame(['fluxter:saas:create-client', 'fx:saas:create-client'], $command->getAliases());
        $this->assertSame('Creates a new SaaS client', $command->getDescription());
        $this->assertTrue($command->getDefinition()->getArgument('url')->isRequired());
    }

    public function testCreatesClient(): void
    {
        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted = $entity;
            });
        $em->expects($this->once())->method('flush');

        // Listeners can ask for additional data, see docs/additional-data-on-tenant-entity.md
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleClientCreationEvent::class, static function (ConsoleClientCreationEvent $event): void {
            $event->getTenant()->setName($event->console()->ask('Name'));
        });

        // The new client is the current tenant while it gets created, e.g. for children created by listeners
        $tenantService = $this->createMock(TenantService::class);
        $tenantService->expects($this->once())
            ->method('setTenant')
            ->with($this->callback(static fn ($client) => $client instanceof Tenant && 'acme.example.com' === $client->getUrl()));

        $tester = new CommandTester(new CreateClientCommand($em, $dispatcher, $tenantService, Tenant::class));
        $tester->setInputs(['ACME Corp.']);
        $tester->execute(['url' => 'acme.example.com']);

        $tester->assertCommandIsSuccessful();
        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('The client has been created successfully.', $tester->getDisplay());

        $this->assertInstanceOf(Tenant::class, $persisted);
        $this->assertSame('acme.example.com', $persisted->getUrl());
        $this->assertSame('ACME Corp.', $persisted->getName());
    }
}
