<?php

namespace NTI\TicketBundle\Service\Ticket;

use JMS\Serializer\SerializationContext;
use NTI\TicketBundle\Entity\Ticket\Entry;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Event\TicketEntryAddedEvent;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Form\Ticket\EntryType;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Model\TicketProcess;
use NTI\TicketBundle\Service\SettingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\User\UserInterface;

class EntryService extends SettingService
{

    const ENTRY_BASE_SERIALIZATION = array("nti_ticket_entries");
    const ENTRY_INTERNAL_SERIALIZATION = array("nti_ticket_entries_internal");
    const ENTRY_CONTACT_SERIALIZATION = array("nti_ticket_contact");
    const ENTRY_RESOURCES_SERIALIZATION = array("nti_ticket_resource");

    private $em;
    private $container;
    private $serializer;
    private $dispatcher;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->container = $container;
        $this->em = $this->getManager();
        $this->serializer = $this->getSerializer();
        $this->dispatcher = $container->get('event_dispatcher');
    }

    /**
     * @param $ticketId
     * @return array|Entry[]
     */
    public function findAllByTicketId($ticketId){
        return $this->em->getRepository(Entry::class)->findBy(array('ticket'=>$ticketId), array('creationDate'=>'asc'));
    }

    /**
     * @param $ticketId
     * @param bool $serialized
     * @return array|Entry[]
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllByTicketIdProcessed($ticketId, $serialized = false){

        $entries = array();
        $data = $this->em->getRepository(Entry::class)->findBy(array('ticket'=>$ticketId), array('creationDate'=>'asc'));

        /** @var Entry $item */
        foreach ($data as $item){
            $entries[] = $this->processEntry($item, $serialized);
        }

        return $entries;
    }

    /**
     * @param $id
     * @return null|object
     */
    public function findById($id)
    {
        return $this->em->getRepository(Entry::class)->find($id);
    }

    /**
     * @param $id
     * @param bool $serialized
     * @return null|object
     */
    public function findByIdProcessed($id, $serialized = false)
    {
        $entry = $this->em->getRepository(Entry::class)->find($id);
    }

    /**
     * @param $ticketId
     * @param $id
     * @return null|object
     */
    public function findByTicketIdAndId($ticketId, $id)
    {
        return $this->em->getRepository(Entry::class)->findOneBy(array('ticket'=>$ticketId, 'id' => $id));
    }

    /**
     * @param $ticketId
     * @param $id
     * @param bool $serialized
     * @return null|object
     */
    public function findByTicketIdAndIdProcessed($ticketId, $id, $serialized = false)
    {
        $entry = $this->em->getRepository(Entry::class)->findOneBy(array('ticket'=>$ticketId, 'id' => $id));
    }

    /**
     * @param Entry $entry
     * @param bool $serialized
     * @return mixed|Entry
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function processEntry(Entry $entry, $serialized = false){

        $resource = null;
        $contact = null;

        if ($entry->getResource()) {
            $resource = $this->container->get('nti_ticket.resource.repository')->getResourceByUniqueId($entry->getResource());

        }elseif ($entry->getContact()) {
            $contact = $this->container->get('nti_ticket.contact.repository')->getContactByUniqueId($entry->getContact());
        }

        if ($serialized){
            // -- entry
            $contact = json_decode($this->container->get('jms_serializer')->serialize($contact, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::ENTRY_CONTACT_SERIALIZATION)), true);
            $resource = json_decode($this->container->get('jms_serializer')->serialize($resource, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::ENTRY_RESOURCES_SERIALIZATION)), true);

            // -- notification
            $notification = null;
            if ($entry->getNotification()){
                $notification = $this->container->get('nti_ticket.notification.service')->processNotification($entry->getNotification());
            }

            $entry = json_decode($this->container->get('jms_serializer')->serialize($entry, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::ENTRY_BASE_SERIALIZATION)), true);
            $entry['contact'] = $contact;
            $entry['resource'] = $resource;
            $entry['notification'] = $notification;


        }else{
            $entry->setResourceManually($resource);
            $entry->setContactManually($contact);
        }

        return $entry;

    }


    /**
     * @param UserInterface $user
     * @param Ticket $ticket
     * @param array $data
     * @return Entry
     * @throws DatabaseException
     * @throws InvalidFormException
     * @throws TicketProcessStoppedException
     */
    public function create(UserInterface $user, Ticket $ticket, $data = array()){

        /** @var TicketProcess $process */
        $process = new TicketProcess();
        $process->setUser($user);
        $process->setTicket($ticket);
        $process->setData($data);

        /**
         * calling pre ticket entry creation method
         */
        $process = $this->container->get($this->getServiceName())->beforeCreateTicketEntry($process);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        $entry = new Entry();
        $entry->setTicket($ticket);

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create(EntryType::class, $entry);
        $form->submit($process->getData());

        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->persist($entry);
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new DatabaseException();
        }

        /**
         * Dispatching the created Ticket Event
         */
        try {
            $event = new TicketEntryAddedEvent($entry, $user);
            $result = $this->dispatcher->dispatch(TicketEntryAddedEvent::TICKET_ENTRY_ADDED, $event);
            if ($result->getRegisterNotification() == true){
                $notification = $result->getNotification();
                $this->em->persist($notification);
                $this->em->flush();
            }
        }catch (\Exception $exception){
            // todo :: improve this.
        }

        return $entry;
    }

    /**
     * @param Email $email
     * @param Ticket $ticket
     * @param array $data
     * @return Entry
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function createFromEmail(Email $email, Ticket $ticket, $data = array()){

        /** @var TicketProcess $process */
        $process = new TicketProcess();
        $process->setEmail($email);
        $process->setTicket($ticket);
        $process->setData($data);

        $entry = new Entry();
        $entry->setTicket($ticket);

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create(EntryType::class, $entry);
        $form->submit($process->getData());

        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $ticket->setIsUnread(true);
            $entry->setSource(Entry::SOURCE_EMAIL_CONNECTOR);
            $this->em->persist($entry);
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new DatabaseException();
        }

        /**
         * Dispatching the created Ticket Event
         */
        try {
            $event = new TicketEntryAddedEvent($entry, null,$email,Entry::SOURCE_EMAIL_CONNECTOR);
            $result = $this->dispatcher->dispatch(TicketEntryAddedEvent::TICKET_ENTRY_ADDED, $event);
            if ($result->getRegisterNotification() == true){
                $notification = $result->getNotification();
                $this->em->persist($notification);
                $this->em->flush();
            }
        }catch (\Exception $exception){
            // what ever that happened here we assume that you simply do not want to track notifications.
        }

        return $entry;
    }


}