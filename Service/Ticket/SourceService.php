<?php

namespace NTI\TicketBundle\Service\Ticket;


use Exception;
use NTI\TicketBundle\Entity\Ticket\Source;
use NTI\TicketBundle\Exception\DatabaseException;
use NTI\TicketBundle\Exception\InternalItemModificationException;
use NTI\TicketBundle\Exception\InvalidFormException;
use NTI\TicketBundle\Form\Ticket\SourceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;

class SourceService
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
     * @return mixed|Source
     * @throws DatabaseException
     * @throws InvalidFormException
     */
    public function create($data = array(), $serialize = false, $formType = SourceType::class)
    {
        $source = new Source();

        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $source);
        $form->submit($data);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->persist($source);
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $source;

        return json_decode($this->container->get('jms_serializer')->serialize($source,'json'));
    }


    /**
     * @param Source $source
     * @param array $data
     * @param bool $isPatch
     * @param bool $serialize
     * @param string $formType
     * @return mixed|Source
     * @throws DatabaseException
     * @throws InternalItemModificationException
     * @throws InvalidFormException
     */
    public function update(Source $source, $data = array(), $isPatch = false, $serialize = false, $formType = SourceType::class)
    {
        if ($source->getIsInternal() == true)
            throw new InternalItemModificationException();

        # -- form validation
        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($formType, $source);
        $form->submit($data, !$isPatch);
        if (!$form->isValid())
            throw new InvalidFormException($form);

        try {
            $this->em->flush();
        } catch (Exception $ex) {
            throw new DatabaseException();
        }

        if (!$serialize)
            return $source;

        return json_decode($this->container->get('jms_serializer')->serialize($source,'json'));
    }


}