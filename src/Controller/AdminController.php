<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\EchangeRepository;
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
    public function make(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository, $echangeRepository);

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
    public function reset(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository, $echangeRepository);
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

    #[Route('/utilisateur/supprimer/{id}', name: 'admin_supprimer_utilisateur', methods: ['POST'])]
    public function supprimerUtilisateur(Request $request, UtilisateurRepository $utilisateurRepository, int $id): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if ($utilisateur == null) {
            $this->addFlash('info', 'Cet utilisateur n\'existe pas.');
            return $this->redirectToRoute('compte_index');
        }

        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');
            $utilisateurRepository->remove($utilisateur, true);
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/utilisateur/{id}/role/{role}', name: 'admin_activer_role_utilisateur', methods: ['POST'])]
    public function activerRoleUtilisateur(Request $request, UtilisateurRepository $utilisateurRepository, int $id, string $role): Response
    {
        $utilisateur = $utilisateurRepository->find($id);

        if ($utilisateur == null) {
            $this->addFlash('info', 'Cet utilisateur n\'existe pas.');
            return $this->redirectToRoute('compte_index');
        }

        if ($this->isCsrfTokenValid('update' . $utilisateur->getId(), $request->request->get('_token'))) {
            if ($role == Utilisateur::NOT_ACTIVE) {
                $utilisateur->setRoles([$role]);
            } else {
                $utilisateur->removeRole(Utilisateur::NOT_ACTIVE)->toggleRole($role);
            }
            $utilisateurRepository->add($utilisateur, true);
            $this->addFlash('success', 'Le role a bien été ajouté.');
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

    private function resetTirage(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): void
    {
        $allUtilisateurs = $utilisateurRepository->findAll();
        foreach ($allUtilisateurs as $utilisateur) {
            $utilisateur->setUtilisateurTire(null);
            $utilisateurRepository->add($utilisateur);
        }
        $echanges = $echangeRepository->findAll();
        foreach ($echanges as $echange) {
            $echangeRepository->remove($echange);
        }
        $doctrine->getManager()->flush();
    }
}
