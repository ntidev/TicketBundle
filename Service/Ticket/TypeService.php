<?php

namespace NTI\TicketBundle\Service\Ticket;


use Exception;
use NTI\TicketBundle\Entity\Ticket\Type;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Form\Ticket\TypeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;

class TypeService
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
     * @return mixed|Type
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function create($data = array(), $serialize = false, $formType = TypeType::class)
    {
        $type = new Type();

        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $type);
        $form->submit($data);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->persist($type);
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $type;

        return json_decode($this->container->get('jms_serializer')->serialize($type,'json'));
    }


    /**
     * @param Type $type
     * @param array $data
     * @param bool $isPatch
     * @param bool $serialize
     * @param string $formType
     * @return mixed|Type
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function update(Type $type, $data = array(), $isPatch = false, $serialize = false, $formType = TypeType::class)
    {
        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $type);
        $form->submit($data, !$isPatch);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $type;

        return json_decode($this->container->get('jms_serializer')->serialize($type,'json'));
    }

}