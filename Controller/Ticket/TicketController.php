<?php

namespace NTI\TicketBundle\Controller\Ticket;


use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\TicketProcessStoppedException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TicketController
 * @package NTI\TicketBundle\Controller\Ticket
 */
class TicketController extends Controller
{
    /**
     * @Route("/", name="nti_ticket_tickets", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return RestResponse
     */
    public function getAction(Request $request)
    {
        /**
         * NO IMPLEMENTED YET
         */
        return new RestResponse(array(), 200);
    }

    /**
     * @Route("/{id}", name="nti_ticket_tickets_get_by_id", methods={"GET"}, options={"expose":true}, requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \NTI\TicketBundle\Exception\ProcessedBoardResourcesException
     * @throws \NTI\TicketBundle\Exception\ProcessedTicketResourcesException
     */
    public function getByIdAction(Request $request, $id)
    {
        $ticket = $this->get('nti_ticket.service')->findByIdProcessed($id, true);
        if (!$ticket) return new RestResponse(null, 404, 'Ticket not found.');

        return new RestResponse($ticket, 200);
    }

    /**
     * @Route("/", name="nti_ticket_tickets_create", methods={"POST"}, options={"expose":true})
     * @param Request $request
     * @return RestResponse
     */
    public function createAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $response = $this->get('nti_ticket.service')->create($this->getUser(), $data);
            $ticket = $this->get('nti_ticket.service')->processTicket($response, true);
            return new RestResponse($ticket, 201, "Ticket successfully created.");
        } catch (\Exception $exception) {
            if ($exception instanceof TicketProcessStoppedException) {
                $process = $exception->getProcess();
                return new RestResponse(null, $exception->getCode(), $exception->getMessage(), $process->getErrors());
            } elseif ($exception instanceof InvalidFormException) {
                return new RestResponse(null, 400, "Form Error.", $exception->getForm());
            } elseif ($exception instanceof DatabaseException) {
                return new RestResponse(null, 500, $exception->getMessage());
//                return new RestResponse(null, 500, "A database error occurred updating the ticket. Please refresh and try again.");
            }
            return new RestResponse(null, 500, "Error: " . $exception->getMessage());
        }
    }

    /**
     * @Route("/{id}", name="nti_ticket_tickets_edit", methods={"PUT","PATCH"}, options={"expose":true}, requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function editAction(Request $request, $id)
    {
        $ticket = $this->get('nti_ticket.service')->findById($id);
        if (!$ticket) return new RestResponse(null, 404, 'Ticket not found.');

        $data = json_decode($request->getContent(), true);
        $method = $request->getRealMethod();

        try {
            $response = $this->get('nti_ticket.service')->edit($this->getUser(), $ticket, $data, $method);
            $ticket = $this->get('nti_ticket.service')->processTicket($response, true);
            return new RestResponse($ticket, 200, "Ticket changes successfully saved.");
        } catch (\Exception $exception) {
            if ($exception instanceof TicketProcessStoppedException) {
                $process = $exception->getProcess();
                return new RestResponse(null, $exception->getCode(), $exception->getMessage(), $process->getErrors());
            } elseif ($exception instanceof InvalidFormException) {
                return new RestResponse(null, 400, "Form Error.", $exception->getForm());
            } elseif ($exception instanceof DatabaseException) {
                return new RestResponse(null, 500, "A database error occurred updating the ticket. Please refresh and try again.");
            }

            return new RestResponse(null, 500, "Error: " . $exception->getMessage());
        }

    }

}