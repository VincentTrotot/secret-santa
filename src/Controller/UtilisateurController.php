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

    #[Route('/listes/modifier/{id}', name: 'compte_modifier_souhait')]
    public function modifierSouhait(Request $request, SouhaitRepository $souhaitRepository, int $id): Response
    {
        $souhait = $souhaitRepository->find($id);

        if ($souhait == null) {
            $this->addFlash('info', 'Ce souhait n\'existe pas.');
            return $this->redirectToRoute('compte_listes');
        }

        if (
            $souhait->getEmetteur() !== $this->getUser() &&
            $souhait->getDestinataire() !== $this->getUser()
        ) {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier ce souhait.');
            return $this->redirectToRoute('compte_listes');
        }


        $form = $this->createForm(SouhaitType::class, $souhait);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'Votre souhait a bien été modifié.');
            return $this->redirectToRoute('compte_listes');
        }

        return $this->renderForm('utilisateur/modifier_souhait.html.twig', [
            'souhait' => $souhait,
            'form' => $form,
        ]);
    }

    #[Route('/listes/supprimer/{id}', name: 'compte_supprimer_souhait', methods: ['POST'])]
    public function delete(Request $request, SouhaitRepository $souhaitRepository, int $id): Response
    {
        $souhait = $souhaitRepository->find($id);

        if ($souhait == null) {
            $this->addFlash('info', 'Ce souhait n\'existe pas.');
            return $this->redirectToRoute('compte_listes');
        }

        if (
            $souhait->getEmetteur() !== $this->getUser() &&
            $souhait->getDestinataire() !== $this->getUser()
        ) {
            $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier ce souhait.');
            return $this->redirectToRoute('compte_listes');
        }
        if ($this->isCsrfTokenValid('delete' . $souhait->getId(), $request->request->get('_token'))) {
            $this->addFlash('success', 'Votre souhait a bien été supprimé.');
            $souhaitRepository->remove($souhait, true);
        }

        return $this->redirectToRoute('compte_listes', [], Response::HTTP_SEE_OTHER);
    }
}
