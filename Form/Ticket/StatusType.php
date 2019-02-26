<?php

namespace NTI\TicketBundle\Form\Ticket;

use NTI\TicketBundle\Entity\Ticket\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('isActive')
            ->add('forClosing')
            ->add('notify')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Status::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }


}
