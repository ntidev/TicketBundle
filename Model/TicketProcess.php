<?php

namespace NTI\TicketBundle\Model;


use NTI\TicketBundle\Entity\Ticket\Entry;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use Symfony\Component\Security\Core\User\UserInterface;

class TicketProcess
{
    /**
     * @var bool $continue
     */
    private $continue = true;

    /**
     * @var Ticket $ticket
     */
    private $ticket;

    /**
     * @var string $method
     */
    private $method;

    /**
     * @var UserInterface $user
     */
    private $user;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var array $additional
     */
    private $additionalData;

    /**
     * @var array $errors
     */
    private $errors;

    /**
     * @var integer $errorCode
     */
    private $responseCode = 200;

    /**
     * @var string $responseMessage
     */
    private $responseMessage;

    /**
     * @var Entry $entry
     */
    private $entry;

    /**
     * @var Email $email
     */
    private $email;

    /**
     * @return bool
     */
    public function isContinue()
    {
        return $this->continue;
    }

    /**
     * @param bool $continue
     * @return TicketProcess
     */
    public function setContinue(bool $continue)
    {
        $this->continue = $continue;
        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     * @return TicketProcess
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return TicketProcess
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     * @return TicketProcess
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return TicketProcess
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     * @return TicketProcess
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return TicketProcess
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     * @return TicketProcess
     */
    public function setResponseCode(int $responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param string $responseMessage
     * @return TicketProcess
     */
    public function setResponseMessage(string $responseMessage)
    {
        $this->responseMessage = $responseMessage;
        return $this;
    }

    /**
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param Entry $entry
     * @return TicketProcess
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        return $this;
    }

    /**
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param Email $email
     * @return TicketProcess
     */
    public function setEmail(Email $email)
    {
        $this->email = $email;
        return $this;
    }






}