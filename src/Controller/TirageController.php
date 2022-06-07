<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tirage')]
class TirageController extends AbstractController
{
    #[Route('/', name: 'tirage_index')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();
        usort($utilisateurs, function ($a, $b) {
            $dateA = $a->getUtilisateurTire()->getDateDeNaissance();
            $dateB = $b->getUtilisateurTire()->getDateDeNaissance();
            return $dateA < $dateB ? -1 : 1;
        });
        return $this->render('tirage/index.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/make', name: 'tirage_make')]
    public function make(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = new ArrayCollection($utilisateurRepository->findAll());
        foreach ($utilisateurs as $utilisateur) {
            $utilisateur->setUtilisateurTire(null);
            $utilisateurRepository->add($utilisateur);
        }
        $doctrine->getManager()->flush();
        do {
            $utilisateurs_copie = clone $utilisateurs;

            $tirage = [];

            foreach ($utilisateurs as $utilisateur) {
                if ($utilisateur->isTiragePossible($utilisateurs_copie)) {
                    $tire = $utilisateur->tire($utilisateurs_copie);
                    $tirage[] = [$utilisateur, $tire];
                    $utilisateurs_copie->removeElement($tire);
                } else {
                    break;
                }
            }
        } while (count($tirage) != count($utilisateurs));

        foreach ($tirage as $tir) {
            $utilisateur = $tir[0];
            $tire = $tir[1];
            $utilisateur->setUtilisateurTire($tire);
            $utilisateurRepository->add($utilisateur);
        }
        $doctrine->getManager()->flush();
        return $this->redirectToRoute('tirage_index');
    }
}
