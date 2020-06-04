<?php

namespace NTI\TicketBundle\Entity\Board;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Board
 *
 * @ORM\Table(name="nti_ticket_board")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Board\BoardRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Board name has already been registered."
 * )
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Board unique Id duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Board
{
    /**
     * @var int
     *
     * @Serializer\Groups({"nti_ticket_board", "nti_ticket_internal", "nti_ticket_board_list"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board", "nti_ticket_internal", "nti_ticket_board_list"})
     * @Serializer\SerializedName("uniqueId")
     * @ORM\Column(name="unique_id", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board", "nti_ticket_board_list"})
     * @Assert\NotBlank(message="Board name field is required")
     * @ORM\Column(name="name", type="string", length=150, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board"})
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @Serializer\Groups({"nti_ticket_board"})
     * @Serializer\SerializedName("isActive")
     * @ORM\Column(name="is_active", type="boolean", options={"default": false})
     */
    private $isActive;

    /**
     * @var bool
     *
     * @Serializer\Groups({"nti_ticket_board"})
     * @Serializer\SerializedName("isLanding")
     * @ORM\Column(name="is_landing", type="boolean", options={"default": false})
     */
    private $isLanding;

    /**
     * @var bool
     *
     * @Serializer\Groups({"nti_ticket_board"})
     * @ORM\Column(name="notify", type="boolean", options={"default": false})
     */
    private $notify;

    /**
     * One Board has Many Resources
     *
     * @var ArrayCollection
     *
     * @Serializer\Groups({"nti_ticket_board","nti_ticket_board_resource"})
     * @ORM\OneToMany(targetEntity="NTI\TicketBundle\Entity\Board\BoardResource", mappedBy="board", cascade={"persist", "remove"})
     */
    private $resources;

    /**
     * @var array
     * @Serializer\SerializedName("eventResources")
     * @Serializer\Groups({"nti_ticket_board"})
     * @ORM\Column(name="event_resources", type="array", nullable=true)
     */
    private $eventResources;


    /**
     * Indicates if the resources ArrayCollection was transformed to the Resource Entity.
     * @var bool
     */
    private $resourcesProcessed;

    /* Email Connector */
    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board"})
     * @Serializer\SerializedName("connectorServer")
     * @ORM\Column(name="connectorServer", type="string", length=255, nullable=true)
     */
    private $connectorServer;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board", "nti_ticket_board_list"})
     * @Serializer\SerializedName("connectorAccount")
     * @ORM\Column(name="connectorAccount", type="string", length=255, nullable=true)
     */
    private $connectorAccount;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_security"})
     * @Serializer\SerializedName("connectorPassword")
     * @ORM\Column(name="connectorPassword", type="string", length=255, nullable=true)
     */
    private $connectorPassword;

    public function __construct()
    {
        $this->resources = new ArrayCollection();
        $this->resourcesProcessed = false;
        $this->eventResources = array();
        $this->isLanding = false;
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
     * @ORM\PrePersist()
     * @return Board
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
     * Set name
     *
     * @param string $name
     *
     * @return Board
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Board
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Board
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set notify
     *
     * @param boolean $notify
     *
     * @return Board
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * Get notify
     *
     * @return bool
     */
    public function getNotify()
    {
        return $this->notify;
    }


    /**
     * Add resource
     *
     * @param \NTI\TicketBundle\Entity\Board\BoardResource $resource
     *
     * @return Board
     */
    public function addResource(\NTI\TicketBundle\Entity\Board\BoardResource $resource)
    {
        if ($this->resources->contains($resource)) return;


        $this->resources[] = $resource;
        $resource->setBoard($this);

        return $this;
    }

    /**
     * Remove resource
     *
     * @param \NTI\TicketBundle\Entity\Board\BoardResource $resource
     */
    public function removeResource(\NTI\TicketBundle\Entity\Board\BoardResource $resource)
    {
        $this->resources->removeElement($resource);
        $resource->setBoard(null);
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
     * Set eventResources
     *
     * @param array $eventResources
     *
     * @return Board
     */
    public function setEventResources($eventResources)
    {
        $this->eventResources = $eventResources;

        return $this;
    }

    /**
     * Get eventResources
     *
     * @return array
     */
    public function getEventResources()
    {
        return $this->eventResources;
    }

    /**
     * Set isLanding
     *
     * @param boolean $isLanding
     *
     * @return Board
     */
    public function setIsLanding($isLanding)
    {
        $this->isLanding = $isLanding;

        return $this;
    }

    /**
     * Get isLanding
     *
     * @return boolean
     */
    public function getIsLanding()
    {
        return $this->isLanding;
    }

    /**
     * @return string
     */
    public function getConnectorServer()
    {
        return $this->connectorServer;
    }

    /**
     * @param string $connectorServer
     * @return Board
     */
    public function setConnectorServer(string $connectorServer)
    {
        $this->connectorServer = $connectorServer;
        return $this;
    }

    /**
     * @return string
     */
    public function getConnectorAccount()
    {
        return $this->connectorAccount;
    }

    /**
     * @param string $connectorAccount
     * @return Board
     */
    public function setConnectorAccount(string $connectorAccount)
    {
        $this->connectorAccount = $connectorAccount;
        return $this;
    }

    /**
     * @return string
     */
    public function getConnectorPassword()
    {
        return $this->connectorPassword;
    }

    /**
     * @param string $connectorPassword
     * @return Board
     */
    public function setConnectorPassword(string $connectorPassword)
    {
        $this->connectorPassword = $connectorPassword;
        return $this;
    }
}
