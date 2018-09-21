<?php

namespace NTI\TicketBundle\Form\Ticket;

use Doctrine\ORM\EntityManagerInterface;
use NTI\TicketBundle\Entity\Configuration\Configuration;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Form\DataTransformer\BoardTransformer;
use NTI\TicketBundle\Form\DataTransformer\PriorityTransformer;
use NTI\TicketBundle\Form\DataTransformer\SourceTransformer;
use NTI\TicketBundle\Form\DataTransformer\StatusTransformer;
use NTI\TicketBundle\Form\DataTransformer\StringToDateTimeTransformer;
use NTI\TicketBundle\Form\DataTransformer\TypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject',TextType::class, array('required' => true, 'invalid_message' => 'Ticket subject is not valid.'))
            ->add('contact', TextType::class, array('required' => true, 'invalid_message' => 'Ticket contact is not valid.'))
            ->add('description')
            ->add('notifyContact')
            ->add('notifyResources')
            ->add('creationResource')
            ->add('notifyCc', TextType::class)
            ->add('requiredBy', TextType::class, array('required' => true, 'invalid_message' => 'Ticket required by date is not valid.'))
            ->add('priority',TextType::class)
            ->add('source', TextType::class, array('required' => true, 'invalid_message' => 'Ticket source is not valid.'))
            ->add('status', TextType::class, array('required' => true, 'invalid_message' => 'Ticket status is not valid.'))
            ->add('type', TextType::class, array('required' => true, 'invalid_message' => 'Ticket type is not valid.'))
            ->add('board', TextType::class, array('required' => true, 'invalid_message' => 'Ticket board is not valid.'));

        // -- data transformers
        $builder->get('priority')->addModelTransformer(new PriorityTransformer($this->em, false));
        $builder->get('source')->addModelTransformer(new SourceTransformer($this->em));
        $builder->get('status')->addModelTransformer(new StatusTransformer($this->em));
        $builder->get('type')->addModelTransformer(new TypeTransformer($this->em));
        $builder->get('board')->addModelTransformer(new BoardTransformer($this->em));
        $builder->get('requiredBy')->addModelTransformer(new StringToDateTimeTransformer(false));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'data_class' => Ticket::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nti_ticketbundle_ticket_ticket';
    }


}
