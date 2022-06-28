<?php

namespace App\Controller;

use App\Entity\Tirage;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\TirageRepository;
use App\Repository\EchangeRepository;
use App\Repository\PronosticRepository;
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

        $chains = [];
        $copie = new ArrayCollection($utilisateurs);

        $i = 0;
        do {
            $i++;
            $link = [];
            $u = $copie->first();
            do {
                $continue = true;
                $link[] = $u;
                if ($copie->contains($u)) {
                    $copie->removeElement($u);
                    $u = $u->getUtilisateurTire();
                } else {
                    $continue = false;
                }
            } while ($continue);
            $chains[] = $link;
        } while (!$copie->isEmpty());

        return $this->render('tirage/reveal.html.twig', [
            'utilisateurs' => $utilisateurs,
            'chains' => $chains,
        ]);
    }

    #[Route('/tirage/make', name: 'tirage_make')]
    public function make(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository, $echangeRepository);

        $utilisateurs = new ArrayCollection($utilisateurRepository->findAllParticipants());

        $possible = true;
        foreach ($utilisateurs as $utilisateur) {
            if (!$utilisateur->isTiragePossible($utilisateurs)) {
                $possible = false;
                break;
            }
        }

        if (!$possible) {
            $this->addFlash('danger', 'Il n\'y a pas assez de participants pour faire un tirage');
            return $this->redirectToRoute('tirage_check');
        }
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

        $json = [];
        foreach ($tirage as $tir) {
            $utilisateur = $tir[0];
            $tire = $tir[1];
            $utilisateur->setUtilisateurTire($tire);
            $utilisateurRepository->add($utilisateur);
            $json[] = serialize($utilisateur);
        }
        // foreach ($json as $data) {
        //     dump(unserialize($data['tirant']));
        //     dump(unserialize($data['tire']));
        // }

        $tirage_log = new Tirage();
        $tirage_log->setCreatedAt(new \DateTimeImmutable());
        $tirage_log->setData($json);
        $doctrine->getManager()->persist($tirage_log);
        $doctrine->getManager()->flush();

        return $this->redirectToRoute('tirage_check');
    }

    #[Route('/tirage/reset', name: 'tirage_reset')]
    public function reset(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): Response
    {
        $this->resetTirage($doctrine, $utilisateurRepository, $echangeRepository);
        return $this->redirectToRoute('tirage_check');
    }

    #[Route('/tirage/logs', name: 'tirage_logs')]
    public function tirageLogs(TirageRepository $tirageRepository): Response
    {
        $tirages = $tirageRepository->findAll();
        return $this->render('tirage/logs.html.twig', [
            'tirages' => $tirages,
        ]);
    }

    #[Route('/tirage/log/{id}', name: 'tirage_log')]
    public function tirageLog(Tirage $tirage): Response
    {
        $json = $tirage->getData();
        $utilisateurs = [];
        foreach ($json as $data) {
            $utilisateurs[] = unserialize($data);
        }

        return $this->render('tirage/log.html.twig', [
            'tirage' => $tirage,
            'utilisateurs' => $utilisateurs,
        ]);
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
            $utilisateur->toggleRole($role);
            $utilisateurRepository->add($utilisateur, true);
            $this->addFlash('success', 'Le role a bien été ajouté.');
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/pronostics/logs', name: 'pronostic_logs')]
    public function pronosticsLogs(PronosticRepository $pronosticRepository): Response
    {
        $pronostics = $pronosticRepository->findAll();
        return $this->render('pronostic/logs.html.twig', [
            'pronostics' => $pronostics,
        ]);
    }

    #[Route('/pronostics/log/{id}', name: 'pronostic_log')]
    public function pronosticLog(int $id, PronosticRepository $pronosticRepository, UtilisateurRepository $utilisateurRepository): Response
    {

        $pronostic = $pronosticRepository->find($id);
        $utilisateurs = $utilisateurRepository->findAllInArray();

        return $this->render('pronostic/log.html.twig', [
            'pronostic' => $pronostic,
            'utilisateurs' => $utilisateurs
        ]);
    }

    private function resetTirage(ManagerRegistry $doctrine, UtilisateurRepository $utilisateurRepository, EchangeRepository $echangeRepository): void
    {
        $allUtilisateurs = $utilisateurRepository->findAll();
        foreach ($allUtilisateurs as $utilisateur) {
            $utilisateur->setUtilisateurTire(null);
            $utilisateur->setPronostic(null);
            $utilisateurRepository->add($utilisateur);
        }
        $echanges = $echangeRepository->findAll();
        foreach ($echanges as $echange) {
            $echangeRepository->remove($echange);
        }
        $doctrine->getManager()->flush();
    }
}
