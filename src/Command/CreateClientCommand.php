<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\Event\ConsoleClientCreationEvent;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(name: 'fluxter:saas:provider:create-client', description: 'Creates a new SaaS client')]
class CreateClientCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $eventDispatcher,
        private string $saasClientEntity,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'The url without www and http')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var TenantInterface */
        $client = new $this->saasClientEntity();
        $client->setUrl($input->getArgument('url'));

        $event = new ConsoleClientCreationEvent($client, new SymfonyStyle($input, $output));
        $this->eventDispatcher->dispatch($event);

        $this->em->persist($client);
        $this->em->flush();

        $output->writeln('The client has been created successfully.');

        return self::SUCCESS;
    }
}
