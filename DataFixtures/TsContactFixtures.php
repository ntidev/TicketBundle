<?php

namespace NTI\TicketBundle\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use NTI\TicketBundle\Resources\doctrine\Entity\TsContact;
use NTI\TicketBundle\Util\Utilities;

class TsContactFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $contact = new TsContact();
        $contact->setName("Test Contact 01");
        $contact->setEmail("contact01@nti_ticket.test");
        $contact->setUniqueId(Utilities::v4UUID());

        $manager->persist($contact);
        $manager->flush();
    }
}