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
            $message .= '<br>';
        }

        $dups = [];
        foreach (array_count_values($pronostic) as $val => $c) {
            if ($c > 1 && $val != 0) {
                $dups[] =  $this->utilisateurRepository->find($val);
            }
        }

        if (count($dups) > 0) {
            foreach ($dups as $dup) {
                $message .=   $this->utilisateurRepository->find($dup) . ' a été tiré plusieurs fois.<br>';
            }
        }

        foreach ($pronostic as $key => $value) {
            $tirant = $this->utilisateurRepository->find($key);
            $tire = $this->utilisateurRepository->find($value);
            if ($tirant->getUtilisateursInterdits()->contains($tire)) {
                $message .= $tirant . ' ne peut pas tirer ' . $tire . '.<br>';
            }
        }

        return $message;
    }

    public function getPronostic(): array
    {
        $pronos = $this->utilisateurRepository->findPronosticForUser($this->id);
        $pronostic = [];
        if (!$pronos == null) {
            foreach ($pronos as $key => $value) {
                $tirant = $this->utilisateurRepository->find($key);
                $tire = $this->utilisateurRepository->find($value);
                $pronostic[] = [
                    $tirant,
                    $tire
                ];
            }
        }
        return $pronostic;
    }
}
