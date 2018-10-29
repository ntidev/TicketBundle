<?php

namespace NTI\TicketBundle\Event;


use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Entity\Ticket\Entry;
use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketEntryAddedEvent extends Event
{
    CONST TICKET_ENTRY_ADDED = 'nti_ticket.ticket.entry.added';
    CONST NAME = 'EVENT_TICKET_ENTRY_ADDED';

    protected $entry;
    protected $user;
    protected $email;
    protected $source;

    private $register = false;
    private $notification;

    /**
     * TicketCreatedEvent constructor.
     *
     * @param Entry $entry
     * @param UserInterface|null $user
     * @param Email|null $email
     * @param string $source
     */
    public function __construct(Entry $entry, UserInterface $user = null, Email $email = null, $source = Entry::SOURCE_SYSTEM )
    {
        $this->entry = $entry;
        $this->user = $user;
        $this->email = $email;
        $this->source = $source;

        $this->notification = new Notification();
        $this->notification->setUniqueId(Utilities::v4UUID());
        $this->notification->setEntry($entry);
        $this->notification->setEventName($this::NAME);
    }

    public function getEntry(){
        return $this->entry;
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

}