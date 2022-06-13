<?php

namespace App\Controller;

use App\Entity\Echange;
use App\Entity\Souhait;
use App\Form\EchangeType;
use App\Form\SouhaitType;
use App\Entity\Utilisateur;
use App\Repository\EchangeRepository;
use App\Repository\SouhaitRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/compte")]
class UtilisateurController extends AbstractController
{
    #[Route('/', name: 'compte_index')]
    public function index(): Response
    {
        return $this->render('utilisateur/index.html.twig');
    }

    #[Route('/listes', name: 'compte_listes')]
    public function listes(UtilisateurRepository $utilisateurRepository): Response
    {
        /** @var $utilisateur Utilisateur */
        $utilisateur = $this->getUser();

        $utilisateurs = $utilisateurRepository->findAllWithThatIdFirst(
            $utilisateur->getUtilisateurTire()?->getId() ?? null
        );

        return $this->render('utilisateur/listes.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/listes/ajouter', name: 'compte_ajouter_souhait')]
    public function ajouterSouhait(Request $request, SouhaitRepository $souhaitRepository): Response
    {

        $souhait = new Souhait();
        $souhait->setEmetteur($this->getUser());
        $souhait->setAchete(false);
        $form = $this->createForm(SouhaitType::class, $souhait);
        $form->add('referer', HiddenType::class, [
            'mapped' => false,
            'attr' => [
                'value' => $request->headers->get('referer'),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'Le souhait a bien été ajouté.');

            $referer = $request->request->all()['souhait']['referer'];
            if ($referer !== "") {
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('compte_index');
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
            return $this->redirectToRoute('compte_index');
        }

        if ($souhait->getAcheteur() != null && $souhait->getAcheteur() != $this->getUser()) {
            if ($souhait->getDestinataire() != $this->getUser()) {
                $this->addFlash('info', 'Seul l\'acheteur peut modifier ce souhait.');
                return $this->redirectToRoute('compte_index');
            }
        }


        $form = $this->createForm(SouhaitType::class, $souhait);
        $form->add('referer', HiddenType::class, [
            'mapped' => false,
            'attr' => [
                'value' => $request->headers->get('referer'),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (
                $souhait->getEmetteur() !== $this->getUser() &&
                $souhait->getDestinataire() !== $this->getUser()
            ) {
                $this->addFlash('danger', 'Vous n\'avez pas le droit de modifier ce souhait.');
                return $this->redirectToRoute('compte_index');
            }

            $souhait->setUpdatedAt(new \DateTimeImmutable());
            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'Le souhait a bien été modifié.');

            $referer = $request->request->all()['souhait']['referer'];
            if ($referer !== "") {
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('compte_index');
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
            $this->addFlash('danger', 'Vous n\'avez pas le droit de supprimer ce souhait.');
            return $this->redirectToRoute('compte_listes');
        }

        if ($this->isCsrfTokenValid('delete' . $souhait->getId(), $request->request->get('_token'))) {
            $this->addFlash('success', 'Votre souhait a bien été supprimé.');
            $souhaitRepository->remove($souhait, true);
        }

        return $this->redirectToRoute('compte_listes', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/listes/acheter/{id}', name: 'compte_acheter_souhait', methods: ['POST'])]
    public function acheter(Request $request, SouhaitRepository $souhaitRepository, int $id): Response
    {
        $souhait = $souhaitRepository->find($id);

        if ($souhait == null) {
            $this->addFlash('info', 'Ce souhait n\'existe pas.');
            return $this->redirectToRoute('compte_listes');
        }

        if ($souhait->isAchete()) {
            $this->addFlash('info', 'Ce souhait a déjà été acheté.');
            return $this->redirectToRoute('compte_listes');
        }

        if ($this->isCsrfTokenValid('buy' . $souhait->getId(), $request->request->get('_token'))) {
            $souhait->setAchete(true);
            $souhait->setAcheteur($this->getUser());
            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'Votre souhait a bien été marqué comme acheté.');
        }

        return $this->redirectToRoute('compte_listes', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/listes/rendre/{id}', name: 'compte_rendre_souhait', methods: ['POST'])]
    public function rendre(Request $request, SouhaitRepository $souhaitRepository, int $id): Response
    {
        $souhait = $souhaitRepository->find($id);

        if ($souhait == null) {
            $this->addFlash('info', 'Ce souhait n\'existe pas.');
            return $this->redirectToRoute('compte_listes');
        }

        if (!$souhait->isAchete()) {
            $this->addFlash('info', 'Ce souhait n\'a pas été acheté.');
            return $this->redirectToRoute('compte_listes');
        }

        if ($this->isCsrfTokenValid('unbuy' . $souhait->getId(), $request->request->get('_token'))) {
            $souhait->setAchete(false);
            $souhait->setAcheteur(null);
            $souhaitRepository->add($souhait, true);
            $this->addFlash('success', 'La marque d\'achat a bien été enlevée.');
        }

        return $this->redirectToRoute('compte_listes', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/echange', name: 'compte_echange')]
    public function echange(Request $request, EchangeRepository $echangeRepository)
    {
        $echanges = $echangeRepository->findBy(['demandeur' => $this->getUser()]);
        foreach ($echanges as $echange) {
            if ($echange->getDemandeur() == $this->getUser() && $echange->getStatus() == 'en_attente') {
                $this->addFlash('danger', 'Vous avez déjà une demande en cours.');
                return $this->redirectToRoute('compte_index');
            }
        }
        $echange = new Echange();
        $echange->setDemandeur($this->getUser());
        $echange->setDate(new \DateTime());
        $echange->setStatus(Echange::STATUS_EN_ATTENTE);
        $form = $this->createForm(EchangeType::class, $echange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $echangeRepository->add($echange, true);
            $this->addFlash('success', 'Votre demande d\'échange a bien été enregistrée.');
            return $this->redirectToRoute('compte_index');
        }

        return $this->renderForm('utilisateur/echange/demande.html.twig', [
            'souhait' => $echange,
            'form' => $form,
        ]);
    }

    #[Route('/echange/refuser/{id}', name: 'compte_refuser_echange', methods: ['POST'])]
    public function refuser(Request $request, EchangeRepository $echangeRepository, int $id): Response
    {
        $echange = $echangeRepository->find($id);

        if ($echange == null) {
            $this->addFlash('info', 'Cette demande d\'échange n\'existe pas.');
            return $this->redirectToRoute('compte_index');
        }

        if ($echange->getStatus() != Echange::STATUS_EN_ATTENTE) {
            $this->addFlash('info', 'Cette demande d\'échange n\'est plus modifiable.');
            return $this->redirectToRoute('compte_index');
        }

        if (!($echange->getDemandeur() == $this->getUser() || $echange->getReceveur() == $this->getUser())) {
            $this->addFlash('info', 'Cette demande d\'échange ne vous appartient pas.');
            return $this->redirectToRoute('compte_index');
        }

        if ($this->isCsrfTokenValid('refuse' . $echange->getId(), $request->request->get('_token'))) {
            if ($echange->getDemandeur() == $this->getUser()) {
                $echange->setStatus(Echange::STATUS_ANNULE);
                $message = 'Votre demande d\'échange a été annulée.';
            } else {
                $echange->setStatus(Echange::STATUS_REFUSE);
                $message = 'La demande d\'échange a été refusée.';
            }
            $echangeRepository->add($echange, true);
            $this->addFlash('success', $message);
        }

        return $this->redirectToRoute('compte_index');
    }

    #[Route('/echange/accepter/{id}', name: 'compte_accepter_echange', methods: ['POST'])]
    public function accepter(Request $request, EchangeRepository $echangeRepository, UtilisateurRepository $utilisateurRepository, ManagerRegistry $doctrine, int $id): Response
    {
        $echange = $echangeRepository->find($id);

        if ($echange == null) {
            $this->addFlash('info', 'Cette demande d\'échange n\'existe pas.');
            return $this->redirectToRoute('compte_index');
        }

        if ($echange->getStatus() != Echange::STATUS_EN_ATTENTE) {
            $this->addFlash('info', 'Cette demande d\'échange n\'est plus modifiable.');
            return $this->redirectToRoute('compte_index');
        }

        if ($echange->getReceveur() != $this->getUser()) {
            $this->addFlash('info', 'Cette demande d\'échange ne vous appartient pas.');
            return $this->redirectToRoute('compte_index');
        }


        if ($this->isCsrfTokenValid('accepte' . $echange->getId(), $request->request->get('_token'))) {
            $label = 'success';
            $message = 'La demande d\'échange a bien été acceptée.';
            if (
                $echange->getDemandeur()->getUtilisateursInterdits()->contains(
                    $echange->getReceveur()->getUtilisateurTire()
                ) ||
                $echange->getReceveur()->getUtilisateursInterdits()->contains(
                    $echange->getDemandeur()->getUtilisateurTire()
                )
            ) {
                $label = 'danger';
                $message = 'Il n\'est pas possible d\'accepter cette demande. Elle a été automatiqement refusée.';
                $echange->setStatus(Echange::STATUS_REFUSE);
            } else {

                $echange->setStatus(Echange::STATUS_ACCEPTE);
                $this->swap($doctrine->getManager(), $echange->getDemandeur(), $echange->getReceveur());
            }
            $echangeRepository->add($echange, true);
            $this->addFlash($label, $message);
        }

        return $this->redirectToRoute('compte_index');
    }

    private function swap(ObjectManager $om, Utilisateur $demandeur, Utilisateur $receveur): void
    {
        $demandeurtire = $receveur->getUtilisateurTire();
        $receveurtire = $demandeur->getUtilisateurTire();

        $demandeur->setUtilisateurTire(null);
        $receveur->setUtilisateurTire(null);

        $om->persist($demandeur);
        $om->persist($receveur);
        $om->flush();

        $receveur->setUtilisateurTire($receveurtire);
        $demandeur->setUtilisateurTire($demandeurtire);

        $om->persist($demandeur);
        $om->persist($receveur);
        $om->flush();
    }
}
