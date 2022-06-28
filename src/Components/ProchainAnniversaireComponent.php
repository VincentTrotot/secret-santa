<?php

namespace App\Components;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('anniversaire')]
class ProchainAnniversaireComponent
{
    public int $id;

    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
    ) {
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateurRepository->findProchainAnniversaire();
    }
}
