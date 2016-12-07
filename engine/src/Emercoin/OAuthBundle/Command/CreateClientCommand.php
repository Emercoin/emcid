<?php

namespace Emercoin\OAuthBundle\Command;

use Emercoin\OAuthBundle\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('emercoin:oauth:create:client')
            ->setDescription('Setup client which gonna auth thought our service')
            ->setHelp('This command helps you to create third party application which gonna be authenticated thought our service')
            ->addArgument('application_name', InputArgument::REQUIRED, 'Application Name')
            ->addArgument('redirect_uri', InputArgument::REQUIRED, 'Redirect URI');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        /** @var Client $client */
        $client = $clientManager->createClient();
        $client->setName($input->getArgument('application_name'));
        $client->setRedirectUris(array($input->getArgument('redirect_uri')));
        $client->setAllowedGrantTypes(array('token', 'authorization_code'));
        $clientManager->updateClient($client);

        $output->writeln('');
        $output->writeln('Client ID: '. $client->getClientId());
        $output->writeln('Client Secret: '. $client->getSecret());
        $output->writeln('Redirect URI: '. $client->getRedirectUris()[0]);
    }
}