<?php

namespace NTI\TicketBundle\Resources\doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * TsContact
 *
 * @ORM\Table(name="nti_ticket_contact_test_only")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Resources\doctrine\Repository\TsContactRepository")
 */
class TsContact
{

    /**
     * @Serializer\Groups({"nti_ticket_contact"})
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_contact"})
     * @Serializer\SerializedName("uniqueId")
     * @ORM\Column(name="unique_id", type="string", length=100, nullable=true, unique=true)
     */
    private $uniqueId;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_contact"})
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @Serializer\Groups({"nti_ticket_contact"})
     * @ORM\Column(type="string", length=254, unique=true)
     */
    private $email;


    /**
     * Get uniqueId
     *
     * @return string The email
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Set uniqueId
     * @param string $uniqueId
     * @return TsContact
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * Get email
     *
     * @return string The email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     * @param string $email
     * @return TsContact
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Returns the email
     *
     * @return string The name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     * @return TsContact
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
