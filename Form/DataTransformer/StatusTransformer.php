<?php

namespace NTI\TicketBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use NTI\TicketBundle\Entity\Ticket\Status;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StatusTransformer implements DataTransformerInterface
{
    private $manager;
    private $required;

    public function __construct(ObjectManager $manager, $required = true)
    {
        $this->manager = $manager;
        $this->required = $required;
    }

    /**
     * @param Status $object
     * @return mixed|string
     */
    public function transform($object)
    {
        if (null === $object) {
            return '';
        }
        return $object->getId();
    }

    /**
     * Transforms a string (number) to an object (Status).
     *
     * @param  array $value
     * @return Status|null
     * @throws TransformationFailedException if object (Status) is not found.
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            if ($this->required)
                throw new TransformationFailedException("The Status value is required.");

            return null;
        }

        $filter = Utilities::arrayFilterByKeys($value, array("id", "uniqueId"));
        if (!$filter) throw new TransformationFailedException("The Status value is invalid.");

        $object = $this->manager->getRepository(Status::class)->findOneBy($filter);
        if (!$object) throw new TransformationFailedException("The Status was not found.");

        return $object;
    }

}