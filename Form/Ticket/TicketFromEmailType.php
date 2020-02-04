<?php

namespace NTI\TicketBundle\Form\Ticket;

use Doctrine\ORM\EntityManagerInterface;
use NTI\TicketBundle\Entity\Ticket\Ticket;
use NTI\TicketBundle\Form\DataTransformer\BoardTransformer;
use NTI\TicketBundle\Form\DataTransformer\PriorityTransformer;
use NTI\TicketBundle\Form\DataTransformer\SourceTransformer;
use NTI\TicketBundle\Form\DataTransformer\StatusTransformer;
use NTI\TicketBundle\Form\DataTransformer\StringToDateTimeTransformer;
use NTI\TicketBundle\Form\DataTransformer\TypeTransformer;
use NTI\TicketBundle\Form\UnstructuredType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketFromEmailType extends AbstractType
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
            ->add('subject')
            ->add('contact')
            ->add('description')
            ->add('notifyContact')
            ->add('notifyResources')
            ->add('creationResource')
            ->add('requiredBy', TextType::class)
            ->add('notifyCc', UnstructuredType::class)
            ->add('priority', UnstructuredType::class)
            ->add('source', UnstructuredType::class)
            ->add('status', UnstructuredType::class)
            ->add('type', UnstructuredType::class)
            ->add('board', UnstructuredType::class);

        // -- data transformers
        $builder->get('priority')->addModelTransformer(new PriorityTransformer($this->em, false));
        $builder->get('source')->addModelTransformer(new SourceTransformer($this->em, false));
        $builder->get('status')->addModelTransformer(new StatusTransformer($this->em, false));
        $builder->get('type')->addModelTransformer(new TypeTransformer($this->em, false));
        $builder->get('board')->addModelTransformer(new BoardTransformer($this->em, false));
        $builder->get('requiredBy')->addModelTransformer(new StringToDateTimeTransformer(false));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Ticket::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false
        ));
    }

}
