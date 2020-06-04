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

class TicketBoardCreatedEvent extends Event
{
    CONST TICKET_BOARD_CREATED = 'nti_ticket.board.created';
    CONST NAME = "EVENT_TICKET_BOARD_CREATED";

    protected $board;
    protected $user;

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
    public function __construct(Board $board, UserInterface $user = null)
    {
        $this->user = $user;
        $this->board = $board;

        $this->notification = new Notification();
        $this->notification->setUniqueId(Utilities::v4UUID());
        $this->notification->setEventName($this::NAME);
    }

    /**
     * @return UserInterface
     */
    public function getUser(){
        return $this->user;
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