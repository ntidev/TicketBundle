<?php

namespace NTI\TicketBundle\Controller\Ticket;


use NTI\TicketBundle\Entity\Ticket\Priority;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TicketPriorityController
 * @package NTI\TicketBundle\Controller\Ticket
 * @Route("/priority")
 */
class TicketPriorityController extends Controller
{

    /**
     * @Route("/", name="nti_ticket_priorities", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request){
        $list = $this->getDoctrine()->getRepository(Priority::class)->findBy(array(), array('name' => 'asc'));
        $list = json_decode($this->get('jms_serializer')->serialize($list, 'json'),true);
        return new JsonResponse($list);
    }

    /**
     * @Route("/{id}", name="nti_ticket_priority_by_id", methods={"GET"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction(Request $request, $id)
    {
        $status = $this->getDoctrine()->getRepository(Priority::class)->find($id);
        if (!$status)
            return new JsonResponse(null, 404);

        $status = json_decode($this->get('jms_serializer')->serialize($status, 'json'));
        return new JsonResponse($status);
    }

}