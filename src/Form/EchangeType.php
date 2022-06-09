<?php

namespace App\Form;

use App\Entity\Echange;
use Symfony\Component\Form\AbstractType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('receveur', null, [
                'label' => 'Avec qui voulez-vous échanger ?',
                'query_builder' => function (UtilisateurRepository $ur) use ($user) {
                    return $ur->createQueryBuilder('u')
                        ->orderBy('u.dateDeNaissance', 'ASC')
                        ->where('u.id != :id')
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
