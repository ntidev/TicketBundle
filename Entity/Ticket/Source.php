<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Source
 *
 * @ORM\Table(name="nti_ticket_source")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\SourceRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Source name has already been registered."
 * )
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Source unique Id duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Source
{
    const INVENTORY_REPORT = 'inventory-report';
    const EMAIL_CONNECTOR = 'email-connector';

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
     * @Assert\NotBlank(message="Ticket Source name field is required")
     * @ORM\Column(name="name", type="string", length=255, unique=true)
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
     * @Serializer\SerializedName("isInternal")
     * @ORM\Column(name="is_internal", type="boolean", options={"default": false})
     */
    private $isInternal;

    public function __construct()
    {
        $this->isInternal = false;
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
     * @return Source
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
     * @return Source
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
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Source
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
     * Set isInternal
     *
     * @param boolean $isInternal
     *
     * @return Source
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
     * Set description
     *
     * @param string $description
     *
     * @return Source
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
}
