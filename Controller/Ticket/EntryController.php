<?php

namespace NTI\TicketBundle\Controller\Ticket;

use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntryController extends Controller
{
    /**
     * @Route("/{ticketId}/entries", name="nti_ticket_entries", methods={"GET"}, options={"expose":true}, requirements={"ticketId"="\d+"})
     * @param Request $request
     * @param $ticketId
     * @return RestResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAction(Request $request, $ticketId)
    {
        $entries = $this->get('nti_ticket.entries.service')->findAllByTicketIdProcessed($ticketId, true);
        return new RestResponse($entries);
    }

    /**
     * @Route("/{ticketId}/entries/{id}", name="nti_ticket_entries_by_id", methods={"GET"}, options={"expose":true}, requirements={"ticketId"="\d+"})
     * @param Request $request
     * @param $ticketId
     * @param $id
     * @return RestResponse
     */
    public function getByIdAction(Request $request, $ticketId, $id)
    {
        $entry = $this->get('nti_ticket.entries.service')->findByTicketIdAndIdProcessed($ticketId, $id);
        if (!$entry)
            return new RestResponse(null,404,"Entry Not Found");

        return new RestResponse($entry);
    }

    /**
     * @Route("/{ticketId}/entries", name="nti_ticket_entries_create", methods={"POST"}, options={"expose":true}, requirements={"ticketId"="\d+"})
     * @param Request $request
     * @param $ticketId
     * @return RestResponse
     */
    public function createAction(Request $request, $ticketId){

        $ticket = $this->get('nti_ticket.service')->findById($ticketId);
        if (!$ticket) return new RestResponse(null, 404,'Ticket not found.');

        $data = json_decode($request->getContent(),true);
        try {
            $response = $this->get('nti_ticket.entries.service')->create($this->getUser(), $ticket, $data);
            $entry = $this->get('nti_ticket.entries.service')->processEntry($response, true);
            return new RestResponse($entry,201,"Ticket entry successfully created.");
        } catch (\Exception $exception){
            if ($exception instanceof TicketProcessStoppedException){
                $process = $exception->getProcess();
                return new RestResponse(null, $exception->getCode(), $exception->getMessage(),$process->getErrors());
            }elseif ($exception instanceof InvalidFormException){
                return new RestResponse(null,400,"Form Error.",$exception->getForm());
            }
            elseif ($exception instanceof DatabaseException){
                return new RestResponse(null,500,"A database error occurred updating the ticket. Please refresh and try again.");
            }

            return new RestResponse(null,500,"Error: ".$exception->getMessage());
        }
    }

}