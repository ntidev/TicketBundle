<?php

namespace NTI\TicketBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use NTI\TicketBundle\Entity\Ticket\Source;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SourceTransformer implements DataTransformerInterface
{
    private $manager;
    private $required;

    public function __construct(ObjectManager $manager, $required = true)
    {
        $this->manager = $manager;
        $this->required = $required;
    }

    /**
     * @param Source $object
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
     * Transforms a string (number) to an object (Source).
     *
     * @param  array $value
     * @return Source|null
     * @throws TransformationFailedException if object (Source) is not found.
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            if ($this->required)
                throw new TransformationFailedException("The Source value is required.");

            return null;
        }

        $filter = Utilities::arrayFilterByKeys($value, array("id", "uniqueId"));
        if (!$filter) throw new TransformationFailedException("The Source value is invalid.");

        $object = $this->manager->getRepository(Source::class)->findOneBy($filter);
        if (!$object) throw new TransformationFailedException("The Board was not found.");

        return $object;
    }

}