<?php

namespace NTI\TicketBundle\Controller\Ticket;

use Exception;
use NTI\TicketBundle\Entity\Ticket\Document;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class TicketDocumentController extends Controller
{

    /**
     * @Route("/{ticketId}/documents/dt", name="nti_ticket_documents_datatable", methods={"POST"}, options={"expose":true}, requirements={"ticketId"="\d+"})
     * @param Request $request
     * @param $ticketId
     * @return JsonResponse|RestResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function documentDataTableAction(Request $request, $ticketId)
    {
        $draw = $request->get('draw');
        $search = $request->get('search');
        $limit = ($request->get('length') != "") ? $request->get('length') : 10;
        $start = ($request->get('start') != "") ? $request->get('start') : 0;
        $orderBy = $request->get('order');
        $columns = $request->get('columns');

        $filters = array();
        foreach ($request->request->all() as $key => $value) {
            if (FALSE !== strpos($key, "filter_")) {
                $filters[str_replace("filter_", "", $key)] = $value;
            }
        }

        $order = null;
        $sortBy = null;
        if (count($orderBy) > 0 && isset($orderBy[0]['column'])) {
            if (is_array($columns) && isset($columns[$orderBy[0]['column']])) {
                $sortBy = $columns[$orderBy[0]['column']]['data'];
            }
            if (isset($orderBy[0]['dir'])) {
                $order = $orderBy[0]['dir'];
            }
        }

        if ($sortBy != "" && $sortBy !== null) {
            switch ($sortBy) {
                default:
                    $sortBy = "document." . $sortBy;
                    break;
            }
        }

        $params = array(
            "start" => $start,
            "limit" => $limit,
            "search" => $search,
            "filters" => $filters,
            "sortBy" => $sortBy,
            "orderBy" => $order
        );

        // -- getting resource config
        $resourceConfig = $this->get('nti_ticket.settings')->getResource();
        $result = $this->getDoctrine()->getRepository(Document::class)->getDocumentsByTicketAndKeywords($params, $ticketId, $resourceConfig);

        $data = array();
        foreach ($result['data'] as $document) {
            $data[] = $this->get('nti_ticket.document.service')->processDocument($document, true);
        }

        return new JsonResponse(array(
            "draw" => intval($draw),
            "recordsTotal" => $result['totalRecords'],
            "recordsFiltered" => $result['totalRecordsFiltered'],
            "data" => $data
        ));
    }

    /**
     * @Route("/{ticketId}/documents", name="nti_ticket_documents", methods={"POST"}, options={"expose":true}, requirements={"ticketId"="\d+"})
     * @param Request $request
     * @param $ticketId
     * @return \NTI\TicketBundle\Entity\Ticket\Document|RestResponse|object
     */
    public function uploadAction(Request $request, $ticketId)
    {
        $ticket = $this->get('nti_ticket.service')->findById($ticketId);
        if (!$ticket) return new RestResponse(null, 404, 'Ticket not found.');

        try {
            $response = $this->get('nti_ticket.document.service')->handleDocumentUpload($this->getUser(), $ticket, $request, true);
            if ($response instanceof RestResponse)
                return $response;

            return new RestResponse($response, 201);
        } catch (\Exception $exception) {
            if ($exception instanceof DatabaseException) {
                return new RestResponse(null, 500, "A database error occurred updating the ticket. Please refresh and try again.");
            }
            return new RestResponse(null, 500, "Error: " . $exception->getMessage());
        }
    }


    /**
     * @Route("/{ticketId}/documents/{id}", name="nti_ticket_documents_download", options={"expose"=true},methods={"GET"}, requirements={"ticketId":"\d+","id":"\d+"})
     * @param $ticketId
     * @param $id
     * @return BinaryFileResponse|JsonResponse
     */
    public function documentDownloadAction($ticketId, $id)
    {

        $ticket = $this->get('nti_ticket.service')->findById($ticketId);
        if (!$ticket) return new RestResponse(null, 404, 'Ticket not found.');

        $document = $this->getDoctrine()->getRepository(Document::class)->findOneBy(array('ticket' => $ticket, 'id' => $id));
        if (!$document) return new RestResponse(null, 404, 'Document not found.');

        // prepare BinaryFileResponse
        $file = $document->getPath() . "/" . $document->getName();
        $response = new BinaryFileResponse($file);
        $response->trustXSendfileTypeHeader();
        $response->headers->set('Content-Type', $document->getType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($file),
            iconv('UTF-8', 'ASCII//TRANSLIT', basename($file))
        );

        return $response;
    }

    /**
     * @Route("/{ticketId}/documents/{id}", name="nti_ticket_documents_delete", options={"expose"=true}, methods={"DELETE"} ,requirements={"ticketId":"\d+","id":"\d+"})
     * @param $ticketId
     * @param $id
     * @return RestResponse
     */
    public function documentDeleteAction($ticketId, $id)
    {

        $ticket = $this->get('nti_ticket.service')->findById($ticketId);
        if (!$ticket) return new RestResponse(null, 404, 'Ticket not found.');

        $document = $this->getDoctrine()->getRepository(Document::class)->findOneBy(array('ticket' => $ticket, 'id' => $id));
        if (!$document) return new RestResponse(null, 404, 'Document not found.');

        // deleting file
        $file = $document->getPath() . "/" . $document->getName();
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($document);
            $em->flush();
            @unlink($file);
            return new RestResponse(null, 200, 'File Deleted');
        } catch (Exception $e) {
            return new RestResponse(null, 500, 'An error occurred deleting the file. Please refresh and try again.');
        }

    }

}