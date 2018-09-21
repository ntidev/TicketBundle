<?php

namespace NTI\TicketBundle\Service\Ticket;


use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use NTI\TicketBundle\Entity\Configuration\Configuration;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Entity\Ticket\TicketResource;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Form\Ticket\TicketFromEmailType;
use NTI\TicketBundle\Form\Ticket\TicketType;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Model\TicketProcess;
use NTI\TicketBundle\Service\SettingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketService extends SettingService
{
    const TICKET_BASE_SERIALIZATION = array("nti_ticket", "nti_ticket_resource");
    const TICKET_CONTACT_SERIALIZATION = array("nti_ticket_contact");
    const TICKET_RESOURCES_SERIALIZATION = array("nti_ticket_resource");
    const TICKET_INTERNAL_SERIALIZATION = array("nti_ticket_internal");

    private $em;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * description: This method look and return the next ticket number by the global configuration.
     * @return int
     */
    public function getNextTicketNumber()
    {
        $nextByConfig = 0;

        // last ticket registered
        $lastTicket = $this->em->getRepository(Ticket::class)->findOneBy(array(), array('ticketNumber' => 'desc'));

        // -- check if the next number was changed.
        $config = $this->em->getRepository(Configuration::class)->findOneBy(array('name' => 'NEXT_TICKET_NUMBER'));

        if ($lastTicket) {

            $nextByTicket = ($lastTicket->getTicketNumber() + 1);

            if ($config)
                $nextByConfig = (intval($config->getValue()) + 1);


            if ($nextByConfig > $nextByTicket)
                return $nextByConfig;

            return $nextByTicket;

        } else {
            // -- its the first ticket

            if ($config)
                $nextByConfig = (intval($config->getValue()) + 1);

            return $nextByConfig;
        }
    }

    /**
     * return a non serialized and non processed ticket.
     * @param $id
     * @return null|Ticket
     */
    public function findById($id)
    {
        return $this->em->getRepository(Ticket::class)->find($id);
    }

    /**
     * return the full processed ticket serialized or not.
     * @param $id
     * @param bool $serialized
     * @return null|Ticket
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \NTI\TicketBundle\Exception\ProcessedBoardResourcesException
     * @throws \NTI\TicketBundle\Exception\ProcessedTicketResourcesException
     */
    public function findByIdProcessed($id, $serialized = false)
    {
        $ticket = $this->findById($id);
        if ($ticket instanceof Ticket) {
            $ticket = $this->processTicket($ticket, $serialized);
        }
        return $ticket;
    }

    /**
     * @param Ticket $ticket
     * @param bool $serialized
     * @return mixed|Ticket
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \NTI\TicketBundle\Exception\ProcessedBoardResourcesException
     * @throws \NTI\TicketBundle\Exception\ProcessedTicketResourcesException
     */
    public function processTicket(Ticket $ticket, $serialized = false)
    {
        $board = null;

        # -- ticket contact info
        $contact = $this->container->get('nti_ticket.contact.repository')->getContactByUniqueId($ticket->getContact());

        # -- get the board info
        if ($ticket->getBoard()) {
            $board = $this->container->get('nti_ticket.board.service')->getByUniqueId($ticket->getBoard()->getUniqueId(), $serialized);
        }

        # -- ticket resources
        $resources = $this->container->get('nti_ticket.resource.repository')->getResourcesByTicket($ticket);
        $creationResource = $this->container->get('nti_ticket.resource.repository')->getResourceByUniqueId($ticket->getCreationResource());

        # -- processing the ticket
        if ($serialized) {
            $contact = json_decode($this->container->get('jms_serializer')->serialize($contact, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::TICKET_CONTACT_SERIALIZATION)), true);
            $resources = json_decode($this->container->get('jms_serializer')->serialize($resources, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::TICKET_BASE_SERIALIZATION)), true);
            $creationResource = json_decode($this->container->get('jms_serializer')->serialize($creationResource, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::TICKET_BASE_SERIALIZATION)), true);
            $ticket = json_decode($this->container->get('jms_serializer')->serialize($ticket, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($this::TICKET_BASE_SERIALIZATION)), true);
            $ticket['contact'] = $contact;
            $ticket['board'] = $board;
            $ticket['resources'] = $resources;
            $ticket['creationResource'] = $creationResource;
        } else {
            $ticket->setContact($contact);
            $ticket->setBoard($board);

            $ticketResources = new ArrayCollection();
            foreach ($resources as $resource){
                if (!$ticketResources->contains($resource))
                    $ticketResources->add($resource);
            }

            $ticket->setResourcesManually($ticketResources);
            $ticket->setCreationResourceManually($creationResource);
        }

        return $ticket;
    }

    /**
     * @param UserInterface $user
     * @param array $data
     * @return Ticket
     * @throws DatabaseException
     * @throws InvalidFormException
     * @throws TicketProcessStoppedException
     */
    public function create(UserInterface $user, $data = array())
    {
        /** @var TicketProcess $process */
        $process = new TicketProcess();
        $process->setUser($user);
        $process->setData($data);

        /**
         * calling pre ticket creation method
         */
        $process = $this->container->get($this->getServiceName())->beforeCreateTicket($process);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        $ticket = new Ticket();

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create(TicketType::class, $ticket);
        $form->submit($process->getData());

        if (!$form->isValid())
            throw new InvalidFormException($form);

        /**
         * handling ticket resources
         */
        $this->handleTicketResources($ticket, array(), $data);

        /**
         * handling next ticket number
         */
        $ticket->setTicketNumber($this->getNextTicketNumber());

        try {
            $this->em->persist($ticket);
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new DatabaseException($exception->getMessage());
        }

        /**
         * calling post ticket creation method
         */
        $process->setTicket($ticket);
        $process = $this->container->get($this->getServiceName())->afterCreateTicket($process);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        return $ticket;
    }

    /**
     * @param UserInterface $user
     * @param Ticket $ticket
     * @param array $data
     * @param string $method
     * @return Ticket
     * @throws DatabaseException
     * @throws InvalidFormException
     * @throws TicketProcessStoppedException
     */
    public function edit(UserInterface $user, Ticket $ticket, $data = array(), $method = "PATCH")
    {
        // -- initial request data
        /** @var TicketProcess $process */
        $process = new TicketProcess();
        $process->setTicket($ticket);
        $process->setUser($user);
        $process->setData($data);
        $process->setMethod($method);
        $isPatch = ($method == "PATCH");

        /**
         * calling pre ticket update method
         */
        $process = $this->container->get($this->getServiceName())->beforeUpdateTicket($process);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        /**
         * keeping the old data for further comparative
         */
        $unchanged = json_decode($this->container->get('jms_serializer')->serialize($ticket, 'json', SerializationContext::create()->setSerializeNull(null)->setGroups($this::TICKET_INTERNAL_SERIALIZATION)), true);

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create(TicketType::class, $ticket);
        $form->submit($process->getData(), !$isPatch);

        if (!$form->isValid())
            throw new InvalidFormException($form);

        /**
         * handling ticket resources
         */
        $this->handleTicketResources($ticket, $unchanged, $data);

        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new DatabaseException();
        }

        /**
         * calling post ticket update method
         */
        $process = $this->container->get($this->getServiceName())->afterUpdateTicket($process, $unchanged);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        return $ticket;
    }

    /**
     * This methods adds or removes resources from the ticket given the received data.
     *
     * @param Ticket $ticket
     * @param array $unchanged
     * @param array $data
     */
    private function handleTicketResources(Ticket $ticket, $unchanged = array(), $data = array())
    {
        $current = array_key_exists('resources', $unchanged) ? $unchanged['resources'] : array();
        $changes = array_key_exists('resources', $data) ? $data['resources'] : array();

        $removed = array_diff($current, $changes);
        $added = array_diff($changes, $current);

        if ($ticket->getId() == null) {

            foreach ($added as $key => $value) {

                if (!is_string($value) && strlen(trim($value)) == 0) continue;

                $resource = new TicketResource();
                $resource->setResource($value);
                $ticket->addResource($resource);
            }

        } else {

            foreach ($removed as $key => $value) {

                if (!is_string($value)) continue;

                /** @var TicketResource $resource */
                $resource = $this->em->getRepository(TicketResource::class)->findOneBy(array('ticket' => $ticket, 'resource' => $value));

                if (!$resource) continue;
                $this->em->remove($resource);
            }

            foreach ($added as $key => $value) {

                if (!is_string($value) && strlen(trim($value)) == 0) continue;

                $resource = $this->em->getRepository(TicketResource::class)->findOneBy(array('ticket' => $ticket, 'resource' => $value));
                if ($resource) continue; // -- duplicated

                $resource = new TicketResource();
                $resource->setResource($value);
                $ticket->addResource($resource);
            }
        }

    }


    # -- ********* EMAIL CONNECTOR OPERATIONS ************ -- #

    /**
     * @param Email $email
     * @return bool
     * @throws DatabaseException
     * @throws InvalidFormException
     * @throws TicketProcessStoppedException
     */
    public function newEmailReceived(Email $email){

        // -- get action for the new email.
        $nextStep = $this->container->get($this->getServiceName())->newEmailFromEmailConnectorReceived($email);
        if (is_array($nextStep)){
            $action = array_key_exists('action', $nextStep) ? $nextStep['action'] : null;
            $data = array_key_exists('data', $nextStep) ? $nextStep['data'] : null;
            $ticket = array_key_exists('ticket', $nextStep) ? $nextStep['ticket'] : null;

            switch ($action){
                case 'create_ticket':
                    if ($data){
                        $this->createFromEmail($email, $data);
                    }
                    break;
                case 'create_entry':
                    if ($data != null && $ticket instanceof Ticket){
                        $this->container->get('nti_ticket.entries.service')->createFromEmail($email, $ticket, $data);
                    }
                    break;
            }
        }
        return true;
    }

    /**
     * @param Email $email
     * @param array $data
     * @return Ticket
     * @throws DatabaseException
     * @throws InvalidFormException
     * @throws TicketProcessStoppedException
     */
    private function createFromEmail(Email $email, $data = array()){

        /** @var TicketProcess $process */
        $process = new TicketProcess();
        $process->setEmail($email);
        $process->setData($data);

        $ticket = new Ticket();

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create(TicketFromEmailType::class, $ticket);
        $form->submit($process->getData());

        if (!$form->isValid())
            throw new InvalidFormException($form);

        /**
         * handling ticket resources
         */
        $this->handleTicketResources($ticket, array(), $data);

        /**
         * handling next ticket number
         */
        $ticket->setTicketNumber($this->getNextTicketNumber());

        try {
            $this->em->persist($ticket);
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new DatabaseException();
        }

        /**
         * calling post ticket creation method
         */
        $process->setTicket($ticket);
        $process = $this->container->get($this->getServiceName())->afterCreateTicket($process);
        if (!$process->isContinue()) {
            throw new TicketProcessStoppedException($process);
        }

        return $ticket;
    }


}