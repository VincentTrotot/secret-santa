<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/compte")]
class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'compte_index')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    #[Route('/listes', name: 'compte_listes')]
    public function listes(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/listes.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
}
