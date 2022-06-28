<?php

namespace App\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use App\Repository\UtilisateurRepository;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository
    ) {
    }
    public function getFunctions()
    {
        return [
            new TwigFunction('tirage', [$this, 'isTirageEnCours']),
        ];
    }

    public function isTirageEnCours(): bool
    {
        $tirage = false;
        $utilisateurs = $this->utilisateurRepository->findAllParticipants();
        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur->getUtilisateurTire() !== null) {
                $tirage = true;
                break;
            }
        }
        return $tirage;
    }
}
