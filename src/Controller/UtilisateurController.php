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
}
