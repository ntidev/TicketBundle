<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Status
 *
 * @ORM\Table(name="nti_ticket_status")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\StatusRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Status name has already been registered."
 * )
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Status unique Id duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Status
{
    /**
     * @var int
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket", "nti_ticket_internal"})
     * @Serializer\SerializedName("uniqueId")
     * @ORM\Column(name="unique_id", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket"})
     * @Assert\NotBlank(message="Ticket Status name field is required")
     * @ORM\Column(name="name", type="string", length=150, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var bool
     * @Serializer\Groups({"nti_ticket"})
     * @Serializer\SerializedName("isActive")
     * @ORM\Column(name="is_active", type="boolean", options={"default": false})
     */
    private $isActive;

    /**
     * @var bool
     * @Serializer\Groups({"nti_ticket"})
     * @Serializer\SerializedName("forClosing")
     * @ORM\Column(name="for_closing", type="boolean", options={"default": false})
     */
    private $forClosing;

    /**
     * @var bool
     *
     * @ORM\Column(name="notify", type="boolean")
     */
    private $notify;


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
     * @return Status
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
     * @return Status
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
     * @return Status
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
     * @return Status
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
     * Set forClosing
     *
     * @param boolean $forClosing
     *
     * @return Status
     */
    public function setForClosing($forClosing)
    {
        $this->forClosing = $forClosing;

        return $this;
    }

    /**
     * Get forClosing
     *
     * @return bool
     */
    public function getForClosing()
    {
        return $this->forClosing;
    }

    /**
     * Set notify
     *
     * @param boolean $notify
     *
     * @return Status
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
}
