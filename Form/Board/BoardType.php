<?php

namespace NTI\TicketBundle\Form\Board;

use NTI\TicketBundle\Entity\Board\Board;
use NTI\TicketBundle\Form\UnstructuredType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardType extends AbstractType
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
            ->add('notify')
            ->add('eventResources', UnstructuredType::class)
            // Email Connector
            ->add('emailConnectorServer')
            ->add('emailConnectorAccount')
            ->add('emailConnectorPassword')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Board::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

}
