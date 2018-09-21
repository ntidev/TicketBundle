<?php

namespace NTI\TicketBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use NTI\TicketBundle\Entity\Ticket\Priority;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PriorityTransformer implements DataTransformerInterface
{
    private $manager;
    private $required;

    public function __construct(ObjectManager $manager, $required = true)
    {
        $this->manager = $manager;
        $this->required = $required;
    }

    /**
     * @param Priority $object
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
     * Transforms a string (number) to an object (Priority).
     *
     * @param  array $value
     * @return Priority|null
     * @throws TransformationFailedException if object (Priority) is not found.
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            if ($this->required)
                throw new TransformationFailedException("The Priority value is required.");

            return null;
        }

        $filter = Utilities::arrayFilterByKeys($value, array("id", "uniqueId"));
        if (!$filter) throw new TransformationFailedException("The Priority value is invalid.");

        $object = $this->manager->getRepository(Priority::class)->findOneBy($filter);
        if (!$object) throw new TransformationFailedException("The Board was not found.");

        return $object;
    }

}