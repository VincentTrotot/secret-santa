<?php

namespace App\Components;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('pronostic')]
class PronosticComponent
{
    public int $id;

    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
    ) {
    }


    public function getErreurs(): string
    {
        $message = '';
        $pronostic = $this->utilisateurRepository->findPronosticForUser($this->id);
        if ($pronostic == null) {
            return $message;
        }
        /** @var Utilisateur */
        $utilisateur = $this->utilisateurRepository->find($this->id);

        if ($pronostic[$this->id] != $utilisateur->getUtilisateurTire()->getId() && $pronostic[$this->id] != 0) {
            $message .= 'Vous n\'avez pas tiré ';
            $message .= $this->utilisateurRepository->find($pronostic[$this->id])->getPrenom();
            $message .= '.<br>';
        }

        $dups = $this->findDups();

        if (count($dups) > 0) {
            foreach ($dups as $dup) {
                $message .=   $dup . ' a été tiré plusieurs fois.<br>';
            }
        }

        foreach ($pronostic as $key => $value) {
            $tirant = $this->utilisateurRepository->find($key);
            $tire = $this->utilisateurRepository->find($value);
            if ($tirant->getUtilisateursInterdits()->contains($tire)) {
                $message .= $tirant . ' ne peut pas tirer ' . $tire . '.<br>';
            }
            if ($tire == $utilisateur->getUtilisateurTire() && $tirant != $utilisateur) {
                $message .= $tirant . ' n\'a pas pu tirer ' . $tire . '.<br>';
            }
        }

        return $message;
    }

    public function getPronostic(): array
    {
        /** @var Utilisateur */
        $utilisateur = $this->utilisateurRepository->find($this->id);

        $pronos = $this->utilisateurRepository->findPronosticForUser($this->id);
        $dups = $this->findDups();
        $pronostic = [];
        if (!$pronos == null) {
            foreach ($pronos as $key => $value) {
                $erreur_tr = false;
                $erreur_td = false;
                $tirant = $this->utilisateurRepository->find($key);
                $tire = $this->utilisateurRepository->find($value);
                if (
                    $tirant->getUtilisateursInterdits()->contains($tire) ||
                    ($tire == $utilisateur->getUtilisateurTire() && $tirant != $utilisateur) ||
                    ($tire != $utilisateur->getUtilisateurTire() && $tirant == $utilisateur)
                ) {
                    $erreur_tr = true;
                }
                if (in_array($tire, $dups)) {
                    $erreur_td = true;
                }
                if ($tire != null) {
                    $pronostic[] = [
                        $tirant,
                        $tire,
                        $erreur_tr,
                        $erreur_td,
                    ];
                }
            }
        }
        return $pronostic;
    }

    private function findDups()
    {
        $pronostic = $this->utilisateurRepository->findPronosticForUser($this->id);
        if ($pronostic == null) {
            return null;
        }
        $dups = [];
        foreach (array_count_values($pronostic) as $val => $c) {
            if ($c > 1 && $val != 0) {
                $dups[] =  $this->utilisateurRepository->find($val);
            }
        }

        return $dups;
    }
}
