<?php

namespace NTI\TicketBundle\Event;

use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Entity\Ticket\Entry;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketCreatedEvent extends Event
{
    CONST TICKET_CREATED = 'nti_ticket.ticket.created';
    CONST NAME = "EVENT_TICKET_CREATED";

    protected $ticket;
    protected $user;
    protected $email;
    protected $source;
    protected $board;

    private $register = false;
    private $notification;

    /**
     * TicketCreatedEvent constructor.
     *
     * @param Ticket $ticket
     * @param UserInterface|null $user
     * @param Email|null $email
     * @param string $source
     */
    public function __construct(Ticket $ticket, UserInterface $user = null, Email $email = null, Board $board = null, $source = Entry::SOURCE_SYSTEM )
    {
        $this->ticket = $ticket;
        $this->user = $user;
        $this->email = $email;
        $this->source = $source;
        $this->board = $board;

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
     * @return Email
     */
    public function getEmail(){
        return $this->email;
    }

    /**
     * @return string
     */
    public function getSource(){
        return $this->source;
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

    /**
     * @return Board
     */
    public function getBoard(): Board
    {
        return $this->board;
    }

    /**
     * @param Board $board
     */
    public function setBoard(Board $board)
    {
        $this->board = $board;
    }


}