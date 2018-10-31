<?php

namespace NTI\TicketBundle\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Notification
 *
 * @ORM\Table(name="nti_ticket_notification")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Notification\NotificationRepository")
 */
class Notification
{
    /**
     * @var int
     *
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\SerializedName("uniqueId")
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="uniqueId", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="contact", type="string", length=100, nullable=true)
     */
    private $contact;

    /**
     * @var array
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="resources", type="array", nullable=true)
     */
    private $resources;

    /**
     * @var array
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="followers", type="array", nullable=true)
     */
    private $followers;

    /**
     * @var array
     * @Serializer\SerializedName("notifiedCc")
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="notified_cc", type="array", nullable=true)
     */
    private $notifiedCc;

    /**
     * @var string
     * @Serializer\SerializedName("eventName")
     * @Serializer\Groups({"nti_ticket_notification", "nti_ticket_entries"})
     * @ORM\Column(name="event_name", type="string", length=100, nullable=false)
     */
    private $eventName;

    /**
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Ticket", inversedBy="notifications")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", nullable=true)
     */
    private $ticket;

    /**
     * @ORM\OneToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Entry", inversedBy="notification")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", nullable=true)
     */
    private $entry;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->resources = array();
        $this->followers = array();
        $this->notifiedCc = array();
    }


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
     * Set uniqueId
     *
     * @param string $uniqueId
     *
     * @return Notification
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    /**
     * Get uniqueId
     *
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Notification
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set resources
     *
     * @param array $resources
     *
     * @return Notification
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set followers
     *
     * @param array $followers
     *
     * @return Notification
     */
    public function setFollowers($followers)
    {
        $this->followers = $followers;

        return $this;
    }

    /**
     * Get followers
     *
     * @return array
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Set notifiedCc
     *
     * @param array $notifiedCc
     *
     * @return Notification
     */
    public function setNotifiedCc($notifiedCc)
    {
        $this->notifiedCc = $notifiedCc;

        return $this;
    }

    /**
     * Get notifiedCc
     *
     * @return array
     */
    public function getNotifiedCc()
    {
        return $this->notifiedCc;
    }

    /**
     * Set eventName
     *
     * @param string $eventName
     *
     * @return Notification
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * Get eventName
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Set ticket
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Ticket $ticket
     *
     * @return Notification
     */
    public function setTicket(\NTI\TicketBundle\Entity\Ticket\Ticket $ticket = null)
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

    /**
     * Set entry
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Entry $entry
     *
     * @return Notification
     */
    public function setEntry(\NTI\TicketBundle\Entity\Ticket\Entry $entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
