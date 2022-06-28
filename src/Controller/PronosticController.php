<?php

namespace App\Controller;

use App\Entity\Pronostic;
use App\Entity\Utilisateur;
use App\Form\PronosticType;
use App\Repository\PronosticRepository;
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
    public function modifier(Request $request, UtilisateurRepository $utilisateurRepository, PronosticRepository $pronosticRepository): Response
    {

        $now = strtotime(date('d-m-Y'));
        $fin = strtotime($this->getParameter('FIN_PRONOSTIC'));
        //dd($now, $fin);

        if ($now > $fin) {
            $this->addFlash('info', 'Vous ne pouvez plus modifier votre pronostic.');
            return $this->redirectToRoute('compte_index');
        }
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

            // log du pronostic

            $pronostic = new Pronostic();
            $pronostic->setCreatedAt(new \DateTimeImmutable());
            $pronostic->setDataUtilisateur([serialize($utilisateur)]);
            $pronosticRepository->add($pronostic, true);

            return $this->redirectToRoute('compte_index');
        }

        return $this->renderForm('pronostic/modifier.html.twig', [
            'pronostic' => $pronostic,
            'form' => $form,
        ]);
    }
}
