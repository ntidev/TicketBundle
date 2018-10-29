<?php

namespace NTI\TicketBundle\Controller\Board;


use JMS\Serializer\SerializationContext;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Exception\ProcessedBoardResourcesException;
use NTI\TicketBundle\Service\Board\BoardService;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BoardController
 * @package NTI\TicketBundle\Controller\Board
 *
 * @Route("/boards")
 */
class BoardController extends Controller
{

    /**
     * @Route("/", name="nti_tickets_boards", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     * @throws \NTI\TicketBundle\Exception\ProcessedBoardResourcesException
     */
    public function getAction(Request $request){

        $data = $this->get('nti_ticket.board.service')->getAll(true);
        return new JsonResponse($data,200);
    }

    /**
     * @Route("/list", name="crm_tickets_board_list", methods={"GET"}, options={"expose":true})
     * @param Request $request
     * @return JsonResponse
     */
    public function getAsListAction(Request $request){
        $boards = $this->getDoctrine()->getRepository(Board::class)->findBy(array(), array('name' => 'asc'));
        $context = SerializationContext::create()->setGroups(BoardService::BOARD_LIST_SERIALIZATION);
        $data = json_decode($this->get('jms_serializer')->serialize($boards,'json', $context),true);
        return new JsonResponse($data,200);
    }

    /**
     * @Route("/{id}", name="nti_ticket_boards_by_id", methods={"GET"}, options={"expose":true}, requirements={"page"="\d+"})
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \NTI\TicketBundle\Exception\ProcessedBoardResourcesException
     */
    public function getByIdAction(Request $request, $id){
        $data = $this->get('nti_ticket.board.service')->getById($id,true);
        return new JsonResponse($data,200);
    }

    /**
     * @Route("/", name="nti_ticket_boards_create", methods={"POST"}, options={"expose":true})
     * @param Request $request
     * @return RestResponse
     */
    public function createAction(Request $request){
        $data = json_decode($request->getContent(), true);

        try {
            $result = $this->get('nti_ticket.board.service')->create($data,true);
            return new RestResponse($result,200,"The new board was successfully created.");
        } catch (\Exception $ex) {
            if ($ex instanceof InvalidFormException){
                return new RestResponse(null,400,"Form Error.",$ex->getForm());
            }elseif ($ex instanceof ProcessedBoardResourcesException){
                return new RestResponse(null,400,"The resources provided could not be processed.");
            }elseif ($ex instanceof DatabaseException){
                return new RestResponse(null,500,"A database error occurred processing the board, check the provided information and try again.");
            }
            return new RestResponse(null,500,"A unknown error occurred processing the board, check the provided information and try again.");
        }
    }

    /**
     * @Route("/{id}", name="nti_ticket_boards_edit", methods={"PUT","PATCH"}, options={"expose":true}, requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return RestResponse
     */
    public function editAction(Request $request, $id)
    {
        $isPatch = $request->getRealMethod() == "PATCH";
        $data = json_decode($request->getContent(), true);

        $board = $this->getDoctrine()->getRepository(Board::class)->find($id);
        if (!$board)
            return new RestResponse(null,404,"Board Not Found.");

        try {
            $result = $this->get('nti_ticket.board.service')->update($board,$data,$isPatch, true);
            return new RestResponse($result,200,"Board changes successfully saved.");
        } catch (\Exception $ex) {
            if ($ex instanceof InvalidFormException){
                return new RestResponse(null,400,"Form Error.",$ex->getForm());
            }elseif ($ex instanceof ProcessedBoardResourcesException){
                return new RestResponse(null,400,"The resources provided could not be processed.");
            }elseif ($ex instanceof DatabaseException){
                return new RestResponse(null,500,"A database error occurred processing the board, check the provided information and try again.");
            }
            return new RestResponse(null,500,"A unknown error occurred processing the board, check the provided information and try again.");
        }
    }

}