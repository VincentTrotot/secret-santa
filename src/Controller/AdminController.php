<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'tirage_check')]
    public function check(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAllParticipants();
        usort($utilisateurs, function ($a, $b) {
            if ($a->getUtilisateurTire() === null) {
                return 1;
            }
            if ($b->getUtilisateurTire() === null) {
                return -1;
            }
            $dateA = $a->getUtilisateurTire()->getDateDeNaissance();
            $dateB = $b->getUtilisateurTire()->getDateDeNaissance();
            return $dateA < $dateB ? -1 : 1;
        });
        return $this->render('tirage/check.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/reveal', name: 'tirage_reveal')]
    public function reveal(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAllParticipants();

        return $this->render('tirage/reveal.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/make', name: 'tirage_make')]
    public function make(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository): Response
    {
        $allUtilisateurs = $utilisateurRepository->findAll();
        $utilisateurs = new ArrayCollection($utilisateurRepository->findAllParticipants());
        foreach ($allUtilisateurs as $utilisateur) {
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

        return $this->redirectToRoute('tirage_check');
    }

    #[Route('/utilisateurs', name: 'admin_utilisateurs')]
    public function utilisateurs(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAll();
        return $this->render('admin/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }
}
