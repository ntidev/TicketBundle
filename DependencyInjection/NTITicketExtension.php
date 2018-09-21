<?php

namespace NTI\TicketBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NTITicketExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $settingServiceDefinition = $container->getDefinition( 'nti_ticket.settings');
        $settingServiceDefinition->addMethodCall( 'setConfig', array($config));

        $resourceServiceDefinition = $container->getDefinition( 'nti_ticket.resource.repository');
        $resourceServiceDefinition->addMethodCall( 'setConfig', array($config));

        $contactServiceDefinition = $container->getDefinition( 'nti_ticket.contact.repository');
        $contactServiceDefinition->addMethodCall( 'setConfig', array($config));

        $ticketServiceDefinition = $container->getDefinition( 'nti_ticket.service');
        $ticketServiceDefinition->addMethodCall( 'setConfig', array($config));

        $entryServiceDefinition = $container->getDefinition( 'nti_ticket.entries.service');
        $entryServiceDefinition->addMethodCall( 'setConfig', array($config));

        $documentServiceDefinition = $container->getDefinition( 'nti_ticket.document.service');
        $documentServiceDefinition->addMethodCall( 'setConfig', array($config));

        // -- setting email client
        $container->setParameter("nti_ticket.email.client", $config["email_client"]);
        $container->setParameter("nti_ticket.documents.dir", $config["documents_directory"]);
    }
}
