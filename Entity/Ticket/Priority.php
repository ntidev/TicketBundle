<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use NTI\TicketBundle\Util\Utilities;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Priority
 *
 * @ORM\Table(name="nti_ticket_priority")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\PriorityRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Priority name has already been registered."
 * )
 * @UniqueEntity(
 *     fields={"uniqueId"},
 *     message="Priority unique Id duplicated."
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Priority
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
     * @Assert\NotBlank(message="Ticket Priority uniqueId field is required")
     * @ORM\Column(name="unique_id", type="string", length=100, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket"})
     * @Assert\NotBlank(message="Ticket Priority name field is required")
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var bool
     * @Serializer\Groups({"nti_ticket"})
     * @Serializer\SerializedName("isActive")
     * @ORM\Column(name="is_active", type="boolean", options={"default": false})
     */
    private $isActive;


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
     * @return Priority
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
     * @return Priority
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
     * @return Priority
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
}
