<?php

namespace NTI\TicketBundle\Repository\Board;
use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Entity\Board\BoardResource;

/**
 * BoardResourceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BoardResourceRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param Board $board
     * @param array $collection
     * @return array
     */
    public function getMultipleByBoardAndUniqueIdCollection(Board $board, array $collection)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('resources')
            ->from(BoardResource::class, 'resources')
            ->andWhere(
                $qb->expr()->eq('resources.board', $board->getId()),
                $qb->expr()->in('resources.resource', $collection)
            );

        return $qb->getQuery()->getResult();
    }
}
