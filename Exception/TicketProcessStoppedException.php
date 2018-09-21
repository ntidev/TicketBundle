<?php

namespace NTI\TicketBundle\Exception;


use NTI\TicketBundle\Model\TicketProcess;
use Throwable;

class TicketProcessStoppedException extends \Exception
{
    private $process;

    public function __construct(TicketProcess $process, Throwable $previous = null)
    {
        $this->process = $process;
        $message = $process->getResponseMessage();
        $code = $process->getResponseCode();
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return TicketProcess
     */
    public function getProcess(){
        return $this->process;
    }

}