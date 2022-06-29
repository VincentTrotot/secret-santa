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
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
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
                        'Non actif' => Utilisateur::NOT_ACTIVE,
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
