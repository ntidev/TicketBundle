<?php

namespace NTI\TicketBundle\Service\Ticket;


use Exception;
use NTI\TicketBundle\Entity\Ticket\Status;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Form\Ticket\StatusType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;

class StatusService
{

    private $em;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @param array $data
     * @param bool $serialize
     * @param string $formType
     * @return mixed|Status
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function create($data = array(), $serialize = false, $formType = StatusType::class)
    {
        $status = new Status();

        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $status);
        $form->submit($data);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->persist($status);
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $status;

        return json_decode($this->container->get('jms_serializer')->serialize($status,'json'));
    }


    /**
     * @param Status $status
     * @param array $data
     * @param bool $isPatch
     * @param bool $serialize
     * @param string $formType
     * @return mixed|Status
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function update(Status $status, $data = array(), $isPatch = false, $serialize = false, $formType = StatusType::class)
    {
        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $status);
        $form->submit($data, !$isPatch);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $status;

        return json_decode($this->container->get('jms_serializer')->serialize($status,'json'));
    }

}