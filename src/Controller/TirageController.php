<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tirage')]
class TirageController extends AbstractController
{
    #[Route('/', name: 'tirage_index')]
    public function index(): Response
    {
        return $this->render('tirage/index.html.twig', [
            'controller_name' => 'TirageController',
        ]);
    }

    #[Route('/make', name: 'tirage_make')]
    public function make(UtilisateurRepository $utilisateurRepository): Response
    {
        $this->makeTirage(new ArrayCollection($utilisateurRepository->findAll()));
        return $this->redirectToRoute('tirage_index');
    }

    private function makeTirage(ArrayCollection $utilisateurs): void
    {
        // WIP

    }
}
