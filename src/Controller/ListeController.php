<?php

namespace App\Controller;

use App\Entity\Souhait;
use App\Form\SouhaitType;
use App\Repository\SouhaitRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/listes")]
class ListeController extends AbstractController
{
    #[Route('', name: 'compte_listes')]
    public function listes(UtilisateurRepository $utilisateurRepository): Response
    {
        /** @var $utilisateur Utilisateur */
        $utilisateur = $this->getUser();

        $utilisateurs = $utilisateurRepository->findAllWithThatIdFirst(
            $utilisateur->getUtilisateurTire()?->getId() ?? null
        );

        return $this->render('liste/listes.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/ajouter', name: 'compte_ajouter_souhait')]
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

        return $this->renderForm('liste/ajouter_souhait.html.twig', [
            'souhait' => $souhait,
            'form' => $form,
        ]);
    }

    #[Route('/modifier/{id}', name: 'compte_modifier_souhait')]
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
                $referer = $request->headers->get('referer');
                if ($referer !== null) {
                    return $this->redirect($referer);
                }
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
                $referer = $request->request->all()['souhait']['referer'];
                if ($referer !== "") {
                    return $this->redirect($referer);
                }
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

        return $this->renderForm('liste/modifier_souhait.html.twig', [
            'souhait' => $souhait,
            'form' => $form,
        ]);
    }

    #[Route('/supprimer/{id}', name: 'compte_supprimer_souhait', methods: ['POST'])]
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

    #[Route('/acheter/{id}', name: 'compte_acheter_souhait', methods: ['POST'])]
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

    #[Route('/rendre/{id}', name: 'compte_rendre_souhait', methods: ['POST'])]
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
}
