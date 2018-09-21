<?php

namespace NTI\TicketBundle\Model;


class Contact
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * Contact constructor.
     * @param null $class
     * @param null $uniqueId
     */
    public function __construct($class = null, $uniqueId = null)
    {
        if ($class != null && $uniqueId != null){
            $this->class = $class;
            $this->uniqueId = $uniqueId;
        }
    }

    /**
     * @return string
     */
    public function getClass(){
        return $this->class;
    }

    /**
     * @param $class
     * @return Contact
     */
    public function setClass($class){
        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getUniqueId(){
        return $this->uniqueId;
    }

    /**
     * @param $uniqueId
     * @return Contact
     */
    public function setUniqueId($uniqueId){
        $this->uniqueId = $uniqueId;
        return $this;
    }

}