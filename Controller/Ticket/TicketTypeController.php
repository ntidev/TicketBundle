<?php

namespace NTI\TicketBundle\Controller\Ticket;


use NTI\TicketBundle\Entity\Ticket\Type;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InternalItemModificationException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TicketController
 * @package NTI\TicketBundle\Controller\Ticket
 * @Route("/types")
 */
class TicketTypeController extends Controller
{
    /**
     * @Route("/", name="nti_ticket_types", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request){
        $list = $this->getDoctrine()->getRepository(Type::class)->findBy(array(), array('name' => 'asc'));
        $list = json_decode($this->get('jms_serializer')->serialize($list, 'json'),true);
        return new JsonResponse($list);
    }

    /**
     * @Route("/{id}", name="nti_ticket_types_by_id", methods={"GET"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getByIdAction(Request $request, $id)
    {
        $type = $this->getDoctrine()->getRepository(Type::class)->find($id);
        if (!$type)
            return new JsonResponse(null, 404);

        $type = json_decode($this->get('jms_serializer')->serialize($type, 'json'));
        return new JsonResponse($type);
    }

    /**
     * @Route("/", name="nti_ticket_types_create", methods={"POST"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request){

        $data = json_decode($request->getContent(), true);

        try {
            $type = $this->get('nti_ticket.type.service')->create($data,true);
            return new RestResponse($type,201);
        }catch (\Exception $ex){
            if ($ex instanceof InvalidFormException){
                return new RestResponse(null,400,"Form Error.",$ex->getForm());
            }elseif ($ex instanceof DatabaseException){
                return new RestResponse(null,500,"A database error occurred processing the ticket status, check the provided information and try again.");
            }
            return new RestResponse(null,500,"A unknown error occurred processing the ticket status, check the provided information and try again.");
        }
    }

    /**
     * @Route("/{id}", name="nti_ticket_types_edit", methods={"PUT","PATCH"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editAction(Request $request, $id)
    {
        $type = $this->getDoctrine()->getRepository(Type::class)->find($id);
        if (!$type)
            return new JsonResponse(null, 404);

        $data = json_decode($request->getContent(), true);
        $isPatch = $request->getRealMethod() == "PATCH";

        try {
            $type = $this->get('nti_ticket.type.service')->update($type, $data, $isPatch, true);
            return new RestResponse($type,200);
        }catch (\Exception $ex){
            if ($ex instanceof InvalidFormException){
                return new RestResponse(null,400,"Form Error.",$ex->getForm());
            }elseif ($ex instanceof DatabaseException){
                return new RestResponse(null,500,"A database error occurred processing the ticket status, check the provided information and try again.");
            } elseif ($ex instanceof InternalItemModificationException) {
                return new RestResponse(null, 400, "Internal ticket type can not be modified.");
            }
            return new RestResponse(null,500,"A unknown error occurred processing the ticket status, check the provided information and try again.");
        }



    }

}