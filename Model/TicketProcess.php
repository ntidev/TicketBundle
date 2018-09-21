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
     * @return Email
     */
    public function getEmail(): ? Email
    {
        return $this->email;
    }

    /**
     * @param Email $email
     */
    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isContinue(): bool
    {
        return $this->continue;
    }

    /**
     * @param bool $continue
     */
    public function setContinue(bool $continue)
    {
        $this->continue = $continue;
    }

    /**
     * @return Ticket
     */
    public function getTicket(): ? Ticket
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     */
    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @return string
     */
    public function getMethod(): ? string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): ? UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): ? array
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ? array
    {
        return $this->errors;
    }

    /**
     * @param array|null $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }


    /**
     * @return int
     */
    public function getResponseCode(): ? int
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     */
    public function setResponseCode(int $responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return string
     */
    public function getResponseMessage(): ? string
    {
        return $this->responseMessage;
    }

    /**
     * @param string $responseMessage
     */
    public function setResponseMessage(string $responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }

    /**
     * @return Entry
     */
    public function getEntry(): ? Entry
    {
        return $this->entry;
    }

    /**
     * @param Entry $entry
     */
    public function setEntry(Entry $entry): void
    {
        $this->entry = $entry;
    }




}