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
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Email
     */
    public function setSubject(string $subject): Email
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Email
     */
    public function setBody(string $body): Email
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return Email
     */
    public function setFrom(string $from): Email
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return MessageType
     */
    public function getMessage(): MessageType
    {
        return $this->message;
    }

    /**
     * @param MessageType $message
     * @return Email
     */
    public function setMessage(MessageType $message): Email
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageEncode(): string
    {
        return $this->messageEncode;
    }

    /**
     * @param string $messageEncode
     * @return Email
     */
    public function setMessageEncode(string $messageEncode): Email
    {
        $this->messageEncode = $messageEncode;
        return $this;
    }



}