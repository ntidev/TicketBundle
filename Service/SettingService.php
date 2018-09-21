<?php

namespace NTI\TicketBundle\Service;


use AppBundle\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * @param array $config
     */
    public function setConfig($config = array())
    {
        $entities = $config['entities'];
        $service = $config['ticket_service'];

        $resourceClass = $entities['resource']['class'];
        $resourceId = $entities['resource']['unique_field'];
        $contactClass = $entities['contact']['class'];
        $contactId = $entities['contact']['unique_field'];

        $this->resourceConfig = new Resource($resourceClass, $resourceId);
        $this->contactConfig = new Contact($contactClass, $contactId);
        $this->service = $service;

    }

    public function getResource(){ return $this->resourceConfig; }
    public function getContact(){ return $this->contactConfig; }
    public function getServiceName(){ return $this->service; }

}