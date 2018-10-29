<?php

namespace NTI\TicketBundle\Entity\Ticket;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Document
 *
 * @ORM\Table(name="nti_ticket_document")
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Ticket\DocumentRepository")
 */
class Document
{
    const ALLOWED_FORMATS = array('PDF', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'TXT', 'RTF', 'IP', 'AR', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP');

    /**
     * @var int
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @Serializer\SerializedName("fileName")
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $fileName;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="format", type="string", length=10)
     */
    private $format;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="type", type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="hash", type="text", nullable=true)
     */
    private $hash;

    /**
     * @var int
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    private $size;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
     * @ORM\Column(name="directory", type="string", length=50)
     */
    private $directory;

    /**
     * @var \DateTime
     * @Serializer\Groups({"nti_ticket_documents"})
     * @Serializer\SerializedName("uploadDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @ORM\Column(name="upload_date", type="datetime")
     */
    private $uploadDate;

    /**
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Ticket\Ticket")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id")
     */
    private $ticket;

    /**
     * @var string
     * @Serializer\Groups({"nti_ticket_documents"})
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
     * Set path
     *
     * @param string $path
     *
     * @return Document
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Document
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
     * Set fileName
     *
     * @param string $fileName
     *
     * @return Document
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set format
     *
     * @param string $format
     *
     * @return Document
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Document
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Document
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return Document
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set directory
     *
     * @param string $directory
     *
     * @return Document
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set uploadDate
     *
     * @param \DateTime $uploadDate
     *
     * @return Document
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return \DateTime
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set resource
     *
     * @param string $resource
     *
     * @return Document
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
     * @return Document
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
}
