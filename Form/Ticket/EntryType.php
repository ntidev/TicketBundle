<?php

namespace NTI\TicketBundle\Form\Ticket;

use NTI\TicketBundle\Entity\Ticket\Entry;
use NTI\TicketBundle\Form\UnstructuredType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message')
            ->add('source')
            ->add('isFrom')
            ->add('resource')
            ->add('contact')
            ->add('email')
            ->add('isInternal')
            ->add('notifyContact')
            ->add('notifyResources')
            ->add('notifyCc', UnstructuredType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Entry::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }


}
