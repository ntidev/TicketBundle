<?php

namespace NTI\TicketBundle\Service;

use NTI\TicketBundle\Model\Contact;
use NTI\TicketBundle\Model\Resource;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingService
{
    /** @var Resource */
    private $resourceConfig;

    /** @var Contact */
    private $contactConfig;

    private $service;

    private $em;

    private $container;

    private $serializer;

    public function __construct(ContainerInterface $container)
    {
        // -- generals
        $this->em = $container->get('doctrine')->getManager();
        $this->container = $container;
        $this->serializer = $container->get('jms_serializer');

        // -- config params
        $resource = $container->getParameter('nti_ticket.entities.resource');
        $contact = $container->getParameter('nti_ticket.entities.contact');

        $resourceClass = $resource['class'];
        $resourceId = $resource['unique_field'];

        $contactClass = $contact['class'];
        $contactId = $contact['unique_field'];

        $this->resourceConfig = new Resource($resourceClass, $resourceId);
        $this->contactConfig = new Contact($contactClass, $contactId);

        $this->service = $container->getParameter('nti_ticket.instance.service');

    }

    public function getResource(){ return $this->resourceConfig; }
    public function getContact(){ return $this->contactConfig; }
    public function getServiceName(){ return $this->service; }
    public function getManager(){ return $this->em; }
    public function getSerializer(){ return $this->serializer; }

}