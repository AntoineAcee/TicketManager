<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AddUserTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u NOT IN (:users)')
                        ->setParameter('users', $options['ticket']->getUsers());
                },
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
            'ticket' => null,
        ]);
    }
}
