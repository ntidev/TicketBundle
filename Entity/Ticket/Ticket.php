<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ticket
 *
 * @ORM\Table(name="nti_ticket")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\TicketRepository")
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Ticket unique id duplicated."
 * )
 * @UniqueEntity(
 *     fields={"ticketNumber"},
 *     message="Ticket number is duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */

class Ticket
{
    /**
     * @var int
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal", "nti_ticket_entries", "nti_ticket_entries_internal"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal", "nti_ticket_entries", "nti_ticket_entries_internal"})
     * @Serializer\SerializedName("uniqueId")
     * @ORM\Column(name="unique_id", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal", "nti_ticket_entries", "nti_ticket_entries_internal"})
     * @Serializer\SerializedName("ticketNumber")
     * @ORM\Column(name="ticket_number", type="string", length=100, unique=true)
     */
    private $ticketNumber;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Assert\NotBlank(message="Ticket subject field is required")
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal","nti_ticket_contact"})
     * @Assert\NotBlank(message="Ticket contact field is required")
     * @ORM\Column(name="contact", type="string", length=150)
     */
    private $contact;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Assert\NotBlank(message="Ticket description field is required")
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var bool
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("notifyContact")
     * @ORM\Column(name="notify_contact", type="boolean", options={"default": false})
     */
    private $notifyContact;

    /**
     * @var bool
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("notifyResources")
     * @ORM\Column(name="notify_resources", type="boolean", options={"default": false})
     */
    private $notifyResources;

    /**
     * @var array
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("notifyCc")
     * @ORM\Column(name="notify_cc", type="array", nullable=true)
     */
    private $notifyCc;

    /**
     * @var \DateTime
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("requiredBy")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @ORM\Column(name="required_by", type="datetime", nullable=true)
     */
    private $requiredBy;

    /**
     * @var \DateTime
     * @Serializer\Groups({"nti_ticket"})
     * @Serializer\SerializedName("creationDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @ORM\Column(name="creation_date", type="datetime")
     */
    private $creationDate;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("creationResource")
     * @ORM\Column(name="creation_resource", type="string", length=150, nullable=true)
     */
    private $creationResource;

    /**
     * @var Priority $priority
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Priority")
     * @ORM\JoinColumn(name="priority_id", referencedColumnName="id", nullable=true)
     **/
    private $priority;

    /**
     * @var Source $source
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Source")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     **/
    private $source;

    /**
     * @var Status $status
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Assert\NotBlank(message="Ticket status field is required")
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     **/
    private $status;

    /**
     * @var Type $type
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     **/
    private $type;

    /**
     * @var Board $board
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Board\Board")
     * @ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=true)
     **/
    private $board;

    /**
     * @var ArrayCollection
     *
     * @Serializer\Groups({"nti_ticket","nti_ticket_resource"})
     * @ORM\OneToMany(targetEntity="NTI\TicketBundle\Entity\Ticket\TicketResource", mappedBy="ticket", cascade={"persist", "remove"})
     */
    private $resources;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="NTI\TicketBundle\Entity\Ticket\Entry", mappedBy="ticket", cascade={"persist", "remove"})
     */
    private $entries;

    /**
     * Indicates if the resources ArrayCollection was transformed to the Resource Entity.
     * @var bool
     */
    private $resourcesProcessed = false;

    /**
     * Ticket constructor.
     */
    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->resources = new ArrayCollection();
    }


    # -- virtual properties

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"nti_ticket_internal"})
     * @Serializer\SerializedName("resources")
     * @return array
     */
    public function getVirtualResources(){
        $resources = array();
        /** @var TicketResource $resource */
        foreach ($this->resources as $resource){
            array_push($resources, $resource->getResource());
        }

        return $resources;
    }


    # -- getters and setters

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
     * @return Ticket
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
     * Set ticketNumber
     *
     * @param string $ticketNumber
     *
     * @return Ticket
     */
    public function setTicketNumber($ticketNumber)
    {
        $this->ticketNumber = $ticketNumber;

        return $this;
    }

    /**
     * Get ticketNumber
     *
     * @return string
     */
    public function getTicketNumber()
    {
        return $this->ticketNumber;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return Ticket
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Ticket
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
     * Set description
     *
     * @param string $description
     *
     * @return Ticket
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set notifyContact
     *
     * @param boolean $notifyContact
     *
     * @return Ticket
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
     * Set requiredBy
     *
     * @param \DateTime $requiredBy
     *
     * @return Ticket
     */
    public function setRequiredBy($requiredBy)
    {
        $this->requiredBy = $requiredBy;

        return $this;
    }

    /**
     * Get requiredBy
     *
     * @return \DateTime
     */
    public function getRequiredBy()
    {
        return $this->requiredBy;
    }

    /**
     * Set creationDate
     *
     * @ORM\PrePersist()
     * @return Ticket
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
     * Set creationResource
     *
     * @param string $creationResource
     *
     * @return Ticket
     */
    public function setCreationResource($creationResource)
    {
        $this->creationResource = $creationResource;

        return $this;
    }

    /**
     * Set creationResource
     *
     * @param $creationResource
     *
     * @return Ticket
     */
    public function setCreationResourceManually($creationResource)
    {
        $this->creationResource = $creationResource;

        return $this;
    }

    /**
     * Get creationResource
     *
     * @return string
     */
    public function getCreationResource()
    {
        return $this->creationResource;
    }

    /**
     * Set priority
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Priority $priority
     *
     * @return Ticket
     */
    public function setPriority(\NTI\TicketBundle\Entity\Ticket\Priority $priority = null)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set source
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Source $source
     *
     * @return Ticket
     */
    public function setSource(\NTI\TicketBundle\Entity\Ticket\Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set status
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Status $status
     *
     * @return Ticket
     */
    public function setStatus(\NTI\TicketBundle\Entity\Ticket\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Type $type
     *
     * @return Ticket
     */
    public function setType(\NTI\TicketBundle\Entity\Ticket\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NTI\TicketBundle\Entity\Ticket\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set board
     *
     * @param \NTI\TicketBundle\Entity\Board\Board $board
     *
     * @return Ticket
     */
    public function setBoard(\NTI\TicketBundle\Entity\Board\Board $board = null)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board
     *
     * @return \NTI\TicketBundle\Entity\Board\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Add resource
     *
     * @param \NTI\TicketBundle\Entity\Ticket\TicketResource $resource
     *
     * @return Ticket
     */
    public function addResource(\NTI\TicketBundle\Entity\Ticket\TicketResource $resource)
    {
        if ($this->resources->contains($resource)) return;

        $this->resources[] = $resource;
        $resource->setTicket($this);

        return $this;
    }

    /**
     * Remove resource
     *
     * @param \NTI\TicketBundle\Entity\Ticket\TicketResource $resource
     */
    public function removeResource(\NTI\TicketBundle\Entity\Ticket\TicketResource $resource)
    {
        $this->resources->removeElement($resource);
        $resource->setTicket(null);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return bool
     */
    public function isResourcesProcessed(){
        return $this->resourcesProcessed;
    }

    /**
     * @param ArrayCollection $resources
     */
    public function setResourcesManually(ArrayCollection $resources){
        $this->resources = $resources;
        $this->resourcesProcessed = true;
    }

    /**
     * Add entry
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Entry $entry
     *
     * @return Ticket
     */
    public function addEntry(\NTI\TicketBundle\Entity\Ticket\Entry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Remove entry
     *
     * @param \NTI\TicketBundle\Entity\Ticket\Entry $entry
     */
    public function removeEntry(\NTI\TicketBundle\Entity\Ticket\Entry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Set notifyResources
     *
     * @param boolean $notifyResources
     *
     * @return Ticket
     */
    public function setNotifyResources($notifyResources)
    {
        $this->notifyResources = $notifyResources;

        return $this;
    }

    /**
     * Get notifyResources
     *
     * @return boolean
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
     * @return Ticket
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
}
