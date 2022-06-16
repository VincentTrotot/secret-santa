<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\PronosticType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/pronostic')]
class PronosticController extends AbstractController
{
    #[Route('', name: 'pronostic')]
    public function index(): Response
    {
        return $this->render('pronostic/index.html.twig', [
            'controller_name' => 'PronosticController',
        ]);
    }

    #[Route('/modifier', name: 'pronostic_modifier')]
    public function modifier(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        /** @var Utilisateur */
        $utilisateur = $this->getUser();
        $pronostic = $utilisateurRepository->findPronosticForUser($utilisateur->getId());

        $form = $this->createForm(PronosticType::class, $pronostic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $request->request->all();

            // $impossibles = [];
            $prono = [];
            foreach ($data['pronostic'] as $key => $value) {
                if ($key !== '_token') {
                    $prono[$key] = (int)$value;
                }
            }
            $utilisateur->setPronostic($prono);
            $utilisateurRepository->add($utilisateur, true);
            $this->addFlash('success', 'Pronostic modifié avec succès');
            return $this->redirectToRoute('compte_index');
        }

        return $this->renderForm('pronostic/modifier.html.twig', [
            'pronostic' => $pronostic,
            'form' => $form,
        ]);
    }
}
