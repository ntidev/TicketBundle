<?php

namespace NTI\TicketBundle\Entity\Board;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * BoardResource
 *
 * @ORM\Table(
 *     name="nti_ticket_board_resources",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="Unique_Board_Resource", columns={"board_id", "resource_id"})}
 * )
 * @ORM\Entity(repositoryClass="NTI\TicketBundle\Repository\Board\BoardResourceRepository")
 */
class BoardResource
{
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
     * @ORM\ManyToOne(targetEntity="NTI\TicketBundle\Entity\Board\Board", inversedBy="resources")
     * @ORM\JoinColumn(name="board_id", referencedColumnName="id", nullable=false)
     */
    private $board;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_ticket_board_resource"})
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
     * @return BoardResource
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
     * Set board
     *
     * @param \NTI\TicketBundle\Entity\Board\Board $board
     *
     * @return BoardResource
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
}
