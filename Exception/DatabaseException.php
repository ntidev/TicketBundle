<?php

namespace NTI\TicketBundle\Exception;


use Throwable;

class DatabaseException extends \Exception
{
    public function __construct(string $message = "", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}