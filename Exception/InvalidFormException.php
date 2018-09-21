<?php

namespace NTI\TicketBundle\Exception;

use Symfony\Component\Form\FormInterface;
use Throwable;

class InvalidFormException extends \Exception
{
    private $form;

    public function __construct(FormInterface $form, int $code = 400, Throwable $previous = null)
    {
        $this->form = $form;
        parent::__construct($message = "", $code, $previous);
    }

    public function getForm()
    {
        return $this->form;
    }

}