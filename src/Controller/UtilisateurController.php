<?php

namespace App\Controller;

use App\Entity\Souhait;
use App\Form\SouhaitType;
use App\Repository\SouhaitRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    #[Route('/listes/ajouter', name: 'compte_ajouter_souhait')]
    public function ajouterSouhait(Request $request, SouhaitRepository $souhaitRepository): Response
    {
        $souhait = new Souhait();
        $souhait->setEmetteur($this->getUser());
        $souhait->setAchete(false);
        $form = $this->createForm(SouhaitType::class, $souhait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'Votre souhait a bien été ajouté.');
            return $this->redirectToRoute('compte_listes');
        }

        return $this->renderForm('utilisateur/ajouter_souhait.html.twig', [
            'souhait' => $souhait,
            'form' => $form,
        ]);
    }
}
