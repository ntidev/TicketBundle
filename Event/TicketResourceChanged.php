<?php

namespace NTI\TicketBundle\Event;


use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketResourceChanged extends Event
{
    CONST TICKET_RESOURCES_CHANGED = 'nti_ticket.ticket.resource.change';
    CONST NAME = 'EVENT_TICKET_RESOURCES_CHANGED';

    protected $ticket;
    protected $user;
    protected $added;
    protected $removed;

    private $register = false;
    private $notification;

    /**
     * TicketResourceChanged constructor.
     * @param Ticket $ticket
     * @param UserInterface|null $user
     * @param array $added
     * @param array $removed
     */
    public function __construct(Ticket $ticket, UserInterface $user = null, $added = array(), $removed = array())
    {
        $this->ticket = $ticket;
        $this->user = $user;
        $this->added = $added;
        $this->removed = $removed;

        $this->notification = new Notification();
        $this->notification->setUniqueId(Utilities::v4UUID());
        $this->notification->setTicket($ticket);
        $this->notification->setEventName($this::NAME);
    }

    /**
     * @return Ticket
     */
    public function getTicket(){
        return $this->ticket;
    }

    /**
     * @return UserInterface
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @return array
     */
    public function getAdded(){
        return $this->added;
    }

    /**
     * @return array
     */
    public function getRemoved(){
        return $this->removed;
    }

    /**
     * @return bool
     */
    public function getRegisterNotification(){
        return $this->register;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setRegisterNotification(Bool $value){
        $this->register = $value;
        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotification(){
        return $this->notification;
    }

}