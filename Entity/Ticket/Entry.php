<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Entity\Notification\Notification;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entry
 *
 * @ORM\Table(name="nti_ticket_entry")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\EntryRepository")
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Ticket unique id duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Entry
{
    CONST IS_FROM_RESOURCE = "resource";
    CONST IS_FROM_CONTACT = "contact";
    CONST IS_FROM_UNKNOWN_EMAIL = "unknown-email";

    CONST SOURCE_SYSTEM = "system";
    CONST SOURCE_EMAIL_CONNECTOR = "email-connector";

    /**
     * @var int
     *
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Serializer\SerializedName("uniqueId")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="unique_id", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="entry_source", type="string", length=100)
     */
    private $source;

    /**
     * @var string
     * @Serializer\SerializedName("isFrom")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="is_from", type="string", length=100)
     */
    private $isFrom;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="resource", type="string", length=100, nullable=true)
     */
    private $resource;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="contact", type="string", length=100, nullable=true)
     */
    private $contact;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var bool
     * @Serializer\SerializedName("isInternal")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="is_internal", type="boolean")
     */
    private $isInternal;

    /**
     * @var bool
     * @Serializer\SerializedName("notifyContact")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="notify_contact", type="boolean")
     */
    private $notifyContact;

    /**
     * @var bool
     * @Serializer\SerializedName("notifyResources")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="notify_resources", type="boolean")
     */
    private $notifyResources;

    /**
     * @var array
     * @Serializer\SerializedName("notifyCc")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="notify_cc", type="array", nullable=true)
     */
    private $notifyCc;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("creationDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @Serializer\Groups({"nti_ticket_entries","nti_ticket_entries_internal"})
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Ticket", inversedBy="entries")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id", nullable=false)
     */
    private $ticket;

    /**
     * @var Notification
     * @Serializer\Groups({"nti_ticket_entries"})
     * @ORM\OneToOne(targetEntity="NTI\TicketBundle\Entity\Notification\Notification", mappedBy="entry")
     */
    private $notification;


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
     * @ORM\PrePersist()
     *
     * @return Entry
     */
    public function setUniqueId()
    {
        $this->uniqueId = Utilities::v4UUID();

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
     * Set message
     *
     * @param string $message
     *
     * @return Entry
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set isFrom
     *
     * @param string $isFrom
     *
     * @return Entry
     */
    public function setIsFrom($isFrom)
    {
        $this->isFrom = $isFrom;

        return $this;
    }

    /**
     * Get isFrom
     *
     * @return string
     */
    public function getIsFrom()
    {
        return $this->isFrom;
    }

    /**
     * Set isInternal
     *
     * @param boolean $isInternal
     *
     * @return Entry
     */
    public function setIsInternal($isInternal)
    {
        $this->isInternal = $isInternal;

        return $this;
    }

    /**
     * Get isInternal
     *
     * @return bool
     */
    public function getIsInternal()
    {
        return $this->isInternal;
    }

    /**
     * Set notifyContact
     *
     * @param boolean $notifyContact
     *
     * @return Entry
     */
    public function setNotifyContact($notifyContact)
    {
        $this->notifyContact = $notifyContact;

        return $this;
    }

    /**
     * Get notifyContact
     *
     * @return bool
     */
    public function getNotifyContact()
    {
        return $this->notifyContact;
    }

    /**
     * Set notifyResources
     *
     * @param boolean $notifyResources
     *
     * @return Entry
     */
    public function setNotifyResources($notifyResources)
    {
        $this->notifyResources = $notifyResources;

        return $this;
    }

    /**
     * Get notifyResources
     *
     * @return bool
     */
    public function getNotifyResources()
    {
        return $this->notifyResources;
    }

    /**
     * Set notifyCc
     *
     * @param array $notifyCc
     *
     * @return Entry
     */
    public function setNotifyCc($notifyCc)
    {
        $this->notifyCc = $notifyCc;

        return $this;
    }

    /**
     * Get notifyCc
     *
     * @return array
     */
    public function getNotifyCc()
    {
        return $this->notifyCc;
    }

    /**
     * Set creationDate
     *
     * @ORM\PrePersist()
     * @return Entry
     */
    public function setCreationDate()
    {
        $this->creationDate = new \DateTime();

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set ticket
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Ticket $ticket
     *
     * @return Entry
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

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return Entry
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
     * Set contact
     *
     * @param string $contact
     *
     * @return Entry
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
     * Set email
     *
     * @param string $email
     *
     * @return Entry
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Entry
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Set notification
     *
     * @param \NTI\TicketBundle\Entity\Notification\Notification $notification
     *
     * @return Entry
     */
    public function setNotification(\NTI\TicketBundle\Entity\Notification\Notification $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \NTI\TicketBundle\Entity\Notification\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    public function setResourceManually($resource){
        $this->resource = $resource;
    }

    public function setContactManually($contact){
        $this->contact = $contact;
    }



}
