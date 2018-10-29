<?php

namespace NTI\TicketBundle\Event;

use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketClosedEvent extends Event
{
    CONST TICKET_CLOSED = 'nti_ticket.ticket.closed';
    CONST NAME = 'EVENT_TICKET_CLOSED';

    protected $ticket;
    protected $user;
    protected $oldStatus;

    private $register = false;
    private $notification;

    public function __construct(Ticket $ticket, UserInterface $user = null, $oldStatus = array())
    {
        $this->ticket = $ticket;
        $this->user = $user;
        $this->oldStatus = $oldStatus;

        $this->notification = new Notification();
        $this->notification->setUniqueId(Utilities::v4UUID());
        $this->notification->setTicket($ticket);
        $this->notification->setEventName($this::NAME);
    }

    public function getTicket(){
        return $this->ticket;
    }

    public function getOldStatus(){
        return $this->oldStatus;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
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