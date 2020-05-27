<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * TicketResource
 *
 * @ORM\Table(
 *     name="nti_ticket_resources",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="Unique_Ticket_Resource", columns={"ticket_id", "resource_id"})}
 * )
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\TicketResourceRepository")
 */
class TicketResource
{
    const EMAIL_RESOURCE = "FROM_EMAIL";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Many Features have One Product.
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Ticket", inversedBy="resources")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", nullable=false)
     */
    private $ticket;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_resource"})
     *
     * @ORM\Column(name="resource_id", type="string", length=100)
     */
    private $resource;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return TicketResource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set ticket
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Ticket $ticket
     *
     * @return TicketResource
     */
    public function setTicket(\NTI\TicketBundle\Entity\Ticket\Ticket $ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get ticket
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
