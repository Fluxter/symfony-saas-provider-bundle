<?php

/*
 * This file is part of the ClanManager package.
 * (c) Fluxter <https://fluxter.net/>
 * Found us at <https://clanmanager.net>
 */

namespace Fluxter\SaasProviderBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Model\SaasClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateClientCommand extends Command
{
    protected static $defaultName = 'fluxter:saas:provider:create-client';

    private string $saasClientEntity;

    private EntityManagerInterface $em;

    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->saasClientEntity = $container->getParameter('saas_provider.client_entity');
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new SaaS client')
            ->addOption(
                'url',
                'u',
                InputOption::VALUE_REQUIRED,
                'The URL for the client (without http and port)',
                null
            );
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        /** @var SaasClientInterface */
        $client = new $this->saasClientEntity();
        $client->setUrl($input->getOption('url'));
        $this->em->persist($client);
        $this->em->flush();

        $output->writeln('The client has been created successfully.');
    }
}
