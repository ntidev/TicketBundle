<?php

namespace NTI\TicketBundle\Interfaces;

use NTI\TicketBundle\Model\Email;
use NTI\TicketBundle\Model\TicketProcess;

/**
 * Interface TicketProcessInterface
 * @package NTI\TicketBundle\Interfaces
 */
interface TicketProcessInterface
{
    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function beforeCreateTicket(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function afterCreateTicket(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function beforeUpdateTicket(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @param array $unchanged
     * @return TicketProcess
     */
    public function afterUpdateTicket(TicketProcess $process, array $unchanged);

    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function beforeCreateTicketEntry(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function afterCreateTicketEntry(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @return TicketProcess
     */
    public function beforeUpdateTicketEntry(TicketProcess $process);

    /**
     * @param TicketProcess $process
     * @param array $unchanged
     * @return TicketProcess
     */
    public function afterUpdateTicketEntry(TicketProcess $process, array $unchanged);

    /**
     * This method get call once a email connector has a new email to be processed. Here you can check the email information
     * and make a decision of create a new ticket, add a new ticket entry or simple don't do anything.
     *
     * Specific action to be returned: anything different will be considered as "none":
     * none: don't do anything.
     * create_ticket: create a new ticket from the email.
     * create_entry: add a new entry to an existing ticket.
     *
     * Example: array('action' => 'create_ticket', 'data' => [])
     *
     * @param Email $email
     * @return array
     */
    public function newEmailFromEmailConnectorReceived(Email $email);

}