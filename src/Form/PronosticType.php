<?php

namespace App\Form;

use App\Entity\Souhait;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PronosticType extends AbstractType
{
    private $utilisateurRepository;
    private $security;

    public function __construct(UtilisateurRepository $utilisateurRepository, Security $security)
    {
        $this->utilisateurRepository = $utilisateurRepository;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Utilisateur */
        $utilisateur =  $this->security->getUser();
        $participants = $this->utilisateurRepository->findAllParticipants();
        $ids = [];
        foreach ($participants as $participant) {
            $ids[$participant->getPrenom()] = $participant->getId();
        }

        $i = 0;
        foreach ($participants as $participant) {
            $builder->add($participant->getId(), ChoiceType::class, [
                'label' => $participant->getPrenom(),
                'choices' => $ids,
                'data' => $utilisateur->getPronosticFor($participant->getID()),
                'required' => false,
                'mapped' => false,
                'expanded' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'pronostic_' . $i,
                ],
            ]);
            $i++;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
