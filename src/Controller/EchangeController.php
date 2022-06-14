<?php

namespace App\Controller;

use App\Entity\Echange;
use App\Form\EchangeType;
use App\Entity\Utilisateur;
use App\Repository\EchangeRepository;
use Doctrine\Persistence\ObjectManager;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/echange')]
class EchangeController extends AbstractController
{
    #[Route('', name: 'compte_echange')]
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

        return $this->renderForm('echange/demande.html.twig', [
            'souhait' => $echange,
            'form' => $form,
        ]);
    }

    #[Route('/refuser/{id}', name: 'compte_refuser_echange', methods: ['POST'])]
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

    #[Route('/accepter/{id}', name: 'compte_accepter_echange', methods: ['POST'])]
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
