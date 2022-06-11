<?php

namespace App\Controller;

use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/tirage', name: 'tirage_check')]
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

    #[Route('/tirage/reveal', name: 'tirage_reveal')]
    public function reveal(UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateurs = $utilisateurRepository->findAllParticipants();

        return $this->render('tirage/reveal.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/tirage/make', name: 'tirage_make')]
    public function make(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository);

        $utilisateurs = new ArrayCollection($utilisateurRepository->findAllParticipants());
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

    #[Route('/tirage/reset', name: 'tirage_reset')]
    public function reset(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository);
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

    #[Route('/utilisateur/modifier/{id}', name: 'admin_modifier_utilisateur')]
    public function modifierUtilisateur(Request $request, UtilisateurRepository $utilisateurRepository, int $id): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if ($utilisateur == null) {
            $this->addFlash('info', 'Cet utilisateur n\'existe pas.');
            return $this->redirectToRoute('compte_index');
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $utilisateur->removeUtilisateursInterdit($utilisateur);
            $utilisateurRepository->add($utilisateur, true);
            $this->addFlash('success', 'L\'utilisateur a été modifié.');
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->renderForm('admin/modifier_utilisateur.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    private function resetTirage(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository): void
    {
        $allUtilisateurs = $utilisateurRepository->findAll();
        foreach ($allUtilisateurs as $utilisateur) {
            $utilisateur->setUtilisateurTire(null);
            $utilisateurRepository->add($utilisateur);
        }
        $doctrine->getManager()->flush();
    }
}
