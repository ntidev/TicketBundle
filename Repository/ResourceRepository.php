<?php

namespace NTI\TicketBundle\Repository;


use Doctrine\ORM\EntityManagerInterface;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Entity\Board\BoardResource;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Exception\ProcessedBoardResourcesException;
use NTI\TicketBundle\Exception\ProcessedTicketResourcesException;
use NTI\TicketBundle\Service\SettingService;

class ResourceRepository extends SettingService
{
    private $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $uniqueId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getResourceByUniqueId($uniqueId)
    {
        // parameters
        $class = $this->getResource()->getClass();
        $field = 'resource.'.$this->getResource()->getUniqueId();

        // query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('resource')
            ->from($class, 'resource')
            ->andWhere(
                $qb->expr()->eq($field, $qb->expr()->literal($uniqueId))
            )->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Board $board
     * @return mixed
     * @throws ProcessedBoardResourcesException
     */
    public function getResourcesByBoard(Board $board)
    {

        if ($board->isResourcesProcessed() == true)
            throw new ProcessedBoardResourcesException();

        if (sizeof($board->getResources()) == 0) return array();

        // parameters
        $class = $this->getResource()->getClass();
        $field = 'resource.' . $this->getResource()->getUniqueId();

        $resources = array();
        /** @var BoardResource $resource */
        foreach ($board->getResources() as $resource) {
            $resources[] = $resource->getResource();
        }

        // query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('resource')
            ->from($class, 'resource')
            ->andWhere($qb->expr()->in($field, $resources));

        return $qb->getQuery()->getResult();
    }


    /**
     * @param Ticket $ticket
     * @return mixed
     * @throws ProcessedTicketResourcesException
     */
    public function getResourcesByTicket(Ticket $ticket)
    {

        if ($ticket->isResourcesProcessed() == true)
            throw new ProcessedTicketResourcesException();

        if (sizeof($ticket->getResources()) == 0) return array();

        // parameters
        $class = $this->getResource()->getClass();
        $field = 'resource.' . $this->getResource()->getUniqueId();

        $resources = array();
        /** @var BoardResource $resource */
        foreach ($ticket->getResources() as $resource) {
            $resources[] = $resource->getResource();
        }

        // query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('resource')
            ->from($class, 'resource')
            ->andWhere($qb->expr()->in($field, $resources));

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $uniqueIdCollection
     * @return mixed
     */
    public function getByUniqueIdCollection($uniqueIdCollection = array())
    {

        if (sizeof($uniqueIdCollection) == 0) return array();

        // parameters
        $class = $this->getResource()->getClass();
        $field = 'resource.' . $this->getResource()->getUniqueId();

        // query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('resource')
            ->from($class, 'resource')
            ->andWhere($qb->expr()->in($field, $uniqueIdCollection));

        return $qb->getQuery()->getResult();
    }

}