<?php

namespace App\Form;

use App\Entity\Souhait;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SouhaitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('destinataire', EntityType::class, [
                'choice_label' => 'prenom',
                'class' => Utilisateur::class,
                'query_builder' => function (UtilisateurRepository $repository) {
                    return $repository->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_PARTICIPANT%')
                        ->orderBy('u.dateDeNaissance', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Souhait::class,
        ]);
    }
}
