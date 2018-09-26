<?php

namespace NTI\TicketBundle\Model;

use garethp\ews\API\Type\MessageType;

class Email
{

    /**
     * @var string $subject
     */
    private $subject;

    /**
     * @var string $body
     */
    private $body;

    /**
     * @var string $from
     */
    private $from;

    /**
     * @var MessageType $message
     */
    private $message;

    /**
     * @var string $messageEncode
     */
    private $messageEncode;

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Email
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Email
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return Email
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return MessageType
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param MessageType $message
     * @return Email
     */
    public function setMessage(MessageType $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageEncode()
    {
        return $this->messageEncode;
    }

    /**
     * @param string $messageEncode
     * @return Email
     */
    public function setMessageEncode(string $messageEncode)
    {
        $this->messageEncode = $messageEncode;
        return $this;
    }



}