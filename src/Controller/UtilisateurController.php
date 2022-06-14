<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/compte")]
class UtilisateurController extends AbstractController
{
    #[Route('', name: 'compte_index')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig');
    }

    #[Route('/tirage', name: 'compte_tirage')]
    public function tirage(UtilisateurRepository $utilisateurRepository): Response
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
        return $this->render('utilisateur/tirage.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }
}
