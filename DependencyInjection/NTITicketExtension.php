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

        // -- setting entities params
        $container->setParameter("nti_ticket.entities.resource", $config["entities"]['resource']);
        $container->setParameter("nti_ticket.entities.contact", $config["entities"]['contact']);

        $container->setParameter("nti_ticket.instance.service", $config["ticket_service"]);
        $container->setParameter("nti_ticket.documents.dir", $config["documents_directory"]);

        $container->setParameter("nti_ticket.documents.dir", $config["documents_directory"]);

        // -- setting email client
        if (array_key_exists('email_connector', $config)){
            $container->setParameter("nti_ticket.email.connector", array_replace_recursive($config["email_connector"], array('provided' => true)));
        }else{
            $container->setParameter("nti_ticket.email.connector", array('provided' => false));
        }
    }
}
