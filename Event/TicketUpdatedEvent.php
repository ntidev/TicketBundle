<?php

namespace NTI\TicketBundle\Event;


use NTI\TicketBundle\Entity\Ticket\Ticket;
use Symfony\Component\EventDispatcher\Event;

class TicketUpdatedEvent extends Event
{
    CONST NAME = 'nti_ticket.ticket.entry.added';

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getTicket(){
        return $this->ticket;
    }

}