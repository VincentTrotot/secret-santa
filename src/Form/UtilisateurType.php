<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', null, [
                'label' => 'Prénom',
            ])
            ->add('nom', null, [
                'label' => 'Nom',
            ])
            ->add('dateDeNaissance', DateTimeType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'js-datepicker',
                    'placeholder' => 'jj/mm/aaaa',
                ],
            ])
            ->add('utilisateursInterdits', EntityType::class, array(
                'class' => Utilisateur::class,

                'label' => 'Utilisateur(s) interdit(s)',
                'expanded' => true,
                'multiple' => true
            ))
            ->add(
                'roles',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'Utilisateur' => Utilisateur::USER,
                        'Spectateur' => Utilisateur::SPECTATEUR,
                        'Participant' => Utilisateur::PARTICIPANT,
                        'Admin' => Utilisateur::ADMIN,
                    ),
                    'label' => 'Role :',
                    'expanded' => true,
                    'multiple' => true
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
