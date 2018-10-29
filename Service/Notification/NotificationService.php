<?php

namespace NTI\TicketBundle\Service\Notification;


use JMS\Serializer\SerializationContext;
use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Service\Ticket\EntryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationService
{
    const NOTIFICATION_BASE_SERIALIZATION = array("nti_ticket_notification");
    const NOTIFICATION_ENTRY_SERIALIZATION = array("nti_ticket_entries");

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Notification $notification
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function processNotification(Notification $notification)
    {
        $contact = null;

        if ($notification->getContact()) {
            $contact = $this->container->get('nti_ticket.contact.repository')->getContactByUniqueId($notification->getContact());
            $contact = json_decode($this->container->get('jms_serializer')->serialize($contact, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(EntryService::ENTRY_CONTACT_SERIALIZATION)), true);
        }

        $resources = $this->container->get('nti_ticket.resource.repository')->getByUniqueIdCollection($notification->getResources());
        $resources = json_decode($this->container->get('jms_serializer')->serialize($resources, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(EntryService::ENTRY_RESOURCES_SERIALIZATION)), true);

        $notification = json_decode($this->container->get('jms_serializer')->serialize($notification, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::NOTIFICATION_BASE_SERIALIZATION)), true);
        $notification['contact'] = $contact;
        $notification['resources'] = $resources;

        return $notification;

    }
}