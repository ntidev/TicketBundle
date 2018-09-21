<?php


namespace NTI\TicketBundle\Model;


class Resource
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
     * Resource constructor.
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
     * @return Resource
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
     * @return Resource
     */
    public function setUniqueId($uniqueId){
        $this->uniqueId = $uniqueId;
        return $this;
    }

}