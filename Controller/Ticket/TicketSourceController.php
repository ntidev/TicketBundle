<?php

namespace NTI\TicketBundle\Controller\Ticket;


use NTI\TicketBundle\Entity\Ticket\Source;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InternalItemModificationException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TicketSourceController
 * @package NTI\TicketBundle\Controller\Ticket
 * @Route("/sources")
 */
class TicketSourceController extends Controller
{

    /**
     * @Route("/", name="nti_ticket_sources", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $list = $this->getDoctrine()->getRepository(Source::class)->findBy(array(), array('name' => 'asc'));
        $list = json_decode($this->get('jms_serializer')->serialize($list, 'json'), true);
        return new JsonResponse($list);
    }

    /**
     * @Route("/{id}", name="nti_ticket_sources_by_id", methods={"GET"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction(Request $request, $id)
    {
        $status = $this->getDoctrine()->getRepository(Source::class)->find($id);
        if (!$status)
            return new JsonResponse(null, 404);

        $status = json_decode($this->get('jms_serializer')->serialize($status, 'json'));
        return new JsonResponse($status);
    }

    /**
     * @Route("/", name="nti_ticket_sources_create", methods={"POST"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {

        $data = json_decode($request->getContent(), true);

        try {
            $status = $this->get('nti_ticket.source.service')->create($data, true);
            return new RestResponse($status, 200);
        } catch (\Exception $ex) {
            if ($ex instanceof InvalidFormException) {
                return new RestResponse(null, 400, "Form Error.", $ex->getForm());
            } elseif ($ex instanceof DatabaseException) {
                return new RestResponse(null, 500, "A database error occurred processing the ticket source, check the provided information and try again.");
            }
            return new RestResponse(null, 500, "A unknown error occurred processing the ticket source, check the provided information and try again.");
        }
    }

    /**
     * @Route("/{id}", name="nti_ticket_sources_edit", methods={"PUT","PATCH"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editAction(Request $request, $id)
    {
        $status = $this->getDoctrine()->getRepository(Source::class)->find($id);
        if (!$status)
            return new JsonResponse(null, 404);

        $data = json_decode($request->getContent(), true);
        $isPatch = $request->getRealMethod() == "PATCH";

        try {
            $status = $this->get('nti_ticket.source.service')->update($status, $data, $isPatch, true);
            return new RestResponse($status, 200);
        } catch (\Exception $ex) {
            if ($ex instanceof InvalidFormException) {
                return new RestResponse(null, 400, "Form Error.", $ex->getForm());
            } elseif ($ex instanceof InternalItemModificationException) {
                return new RestResponse(null, 400, "Internal sources can not be modified.");
            } elseif ($ex instanceof DatabaseException) {
                return new RestResponse(null, 500, "A database error occurred processing the ticket source, check the provided information and try again.");
            }
            return new RestResponse(null, 500, "A unknown error occurred processing the ticket source, check the provided information and try again.");
        }
    }
}