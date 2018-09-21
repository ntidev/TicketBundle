<?php

namespace NTI\TicketBundle\Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StringToDateTimeTransformer implements DataTransformerInterface
{
    private $required;
    private $format;

    /**
     * StringToDateTransformer constructor.
     * @param bool $required
     * @param string $format
     */
    public function __construct($required = true, $format = "m/d/Y H:i:s A") {
        $this->required = $required;
        $this->format = $format;
    }

    /**
     * @param mixed $date
     * @return mixed|string
     */
    public function transform($date)
    {

        if(null === $date) {
            return '';
        }

        return $date->format($this->format);
    }


    /**
     * @param mixed $dateString
     * @return \DateTime|mixed|null
     */
    public function reverseTransform($dateString)
    {

        if(empty($dateString) || null === $dateString) {
            if(!$this->required) {
                return null;
            }
            throw new TransformationFailedException("Invalid date provided");
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $ex) {
            throw new TransformationFailedException("Invalid date provided");
        }

        return $date;
    }

}