<?php

namespace App\Form;

use App\Entity\Echange;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EchangeType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var $user Utilisateur */
        $user = $this->security->getUser();

        $builder
            ->add('receveur', EntityType::class, [
                'class' => Utilisateur::class,
                'label' => 'Avec qui voulez-vous échanger ?',
                'choice_label' => 'prenom',
                'query_builder' => function (UtilisateurRepository $ur) use ($user) {
                    return $ur->createQueryBuilder('u')
                        ->orderBy('u.dateDeNaissance', 'ASC')
                        ->where('u.id != :id')
                        ->andWhere('u.roles LIKE :role')
                        ->setParameter('role', '%ROLE_PARTICIPANT%')
                        ->setParameter('id', $user->getId());
                },
                'attr' => [
                    'class' => 'select2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Echange::class,
        ]);
    }
}
