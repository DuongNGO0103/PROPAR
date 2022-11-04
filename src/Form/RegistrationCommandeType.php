<?php

namespace App\Form;

use DateTime;
use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Operation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class RegistrationCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCommande')
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'data' => new DateTime(),
                'attr' => array('class' => 'form-control', 'style' => 'line-height: 20px;')
            ))
            ->add('operation', EntityType::class, [
                'class' => Operation::class,
                'choice_label' => 'type_operation',
                'constraints' => [
                    new NotNull()
                ],
                'required' => false
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'email',
                'constraints' => [
                    new NotNull()
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
