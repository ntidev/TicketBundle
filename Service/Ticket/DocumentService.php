<?php

namespace NTI\TicketBundle\Service\Ticket;


use Exception;
use JMS\Serializer\SerializationContext;
use NTI\TicketBundle\Entity\Ticket\Document;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Service\SettingService;
use NTI\TicketBundle\Util\Rest\RestResponse;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class DocumentService extends SettingService
{

    private $em;
    private $container;
    private $serializer;
    private $directory;

    CONST DOCUMENT_BASE_SERIALIZATION = array("nti_ticket_documents");

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->serializer = $container->get('jms_serializer');
        $this->directory = $container->getParameter('nti_ticket.documents.dir');
    }


    /**
     * @param Document $document
     * @param bool $serialized
     * @return mixed|Document
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function processDocument(Document $document, $serialized = false)
    {
        $resource = $this->container->get('nti_ticket.resource.repository')->getResourceByUniqueId($document->getResource());
        if ($serialized) {
            $resource = json_decode($this->serializer->serialize($resource, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(array("nti_ticket_resource"))), true);
            $document = json_decode($this->serializer->serialize($document, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(self::DOCUMENT_BASE_SERIALIZATION)), true);
            $document['resource'] = $resource;
            return $document;
        }
    }


    /**
     * @param UserInterface $user
     * @param Ticket $ticket
     * @param Request $request
     * @param bool $serialized
     * @return Document|RestResponse|object
     * @throws DatabaseException
     */
    public function handleDocumentUpload(UserInterface $user, Ticket $ticket, Request $request, $serialized = false)
    {
        if (!$this->directory)
            return new RestResponse("Invalid document directory provided.", 400);

        $directory = $this->directory . "/" . $ticket->getId();

        $data = Utilities::fileUpload($request, $directory);
        if ($data instanceof RestResponse)
            return $data;

        $user = json_decode($this->serializer->serialize($user, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups(array("nti_ticket_resource"))), true);
        if (!array_key_exists($this->getResource()->getUniqueId(), $user))
            return new RestResponse("Invalid resource.", 400);

        $resource = $user[$this->getResource()->getUniqueId()];

        # -- adding document to the company
        $document = new Document();
        $document->setTicket($ticket);
        $document->setDirectory($ticket->getId());
        $document->setFileName($data->name);
        $document->setFormat($data->format);
        $document->setHash($data->hash);
        $document->setName($data->filename);
        $document->setPath($data->path);
        $document->setType($data->type);
        $document->setSize($data->size);
        $document->setUploadDate(new \DateTime());
        $document->setResource($resource);

        try {
            $this->em->persist($document);
            $this->em->flush();

            if ($serialized) {
                $document = json_decode($this->serializer->serialize($document, 'json'), true);
                $document['resource'] = $user;
                return $document;
            }
            return $document;
        } catch (Exception $e) {
            @unlink($data->filePath);
            throw new DatabaseException();
        }

    }


}