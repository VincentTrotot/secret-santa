<?php

namespace App\Controller;

use App\Entity\Utilisateur;
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

    #[Route('/anniversaires', name: 'anniversaires')]
    public function anniversaires(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateursRep = $utilisateurRepository->findAllParticipantsEtSpectateurs();
        $utilisateurs = [];
        foreach ($utilisateursRep as $utilisateur) {
            $utilisateurs[] = [
                $utilisateur,
                new \DateTime(UtilisateurRepository::get_next_birthday($utilisateur->getDateDeNaissance())),

            ];
        }
        return $this->render('utilisateur/anniversaires.html.twig', [
            'utilisateurs' => $utilisateurs
        ]);
    }
}
