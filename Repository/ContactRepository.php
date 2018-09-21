<?php

namespace NTI\TicketBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use NTI\TicketBundle\Service\SettingService;

class ContactRepository extends SettingService
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
    public function getContactByUniqueId($uniqueId)
    {
        // parameters
        $class = $this->getContact()->getClass();
        $field = 'contact.'.$this->getContact()->getUniqueId();

        // query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('contact')
            ->from($class, 'contact')
            ->andWhere(
                $qb->expr()->eq($field, $qb->expr()->literal($uniqueId))
            )->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}