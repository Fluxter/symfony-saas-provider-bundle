<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\Event\ConsoleClientCreationEvent;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand("fluxter:saas:provider:create-client", 'Creates a new SaaS client')]
class CreateClientCommand extends Command
{
    protected static $defaultName = '';

    private string $saasClientEntity;

    public function __construct(
        ParameterBagInterface $paramBag,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TenantService $tenantService
    ) {
        parent::__construct();
        $this->saasClientEntity = $paramBag->get('saas_provider.client_entity');
    }

    protected function configure()
    {
        $this
            ->setAliases([
                "fluxter:saas:create-client",
                "fx:saas:create-client"
            ])
            ->addArgument('url', InputArgument::REQUIRED, 'The url without www and http')
        ;
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
        /** @var TenantInterface */
        $client = new $this->saasClientEntity();
        $client->setUrl($input->getArgument('url'));
        $this->tenantService->setTenant($client);

        $event = new ConsoleClientCreationEvent($client, new SymfonyStyle($input, $output));
        $this->eventDispatcher->dispatch($event);

        $this->em->persist($client);
        $this->em->flush();

        $output->writeln('The client has been created successfully.');

        return self::SUCCESS;
    }
}
