<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $utilisateurs = new ArrayCollection();

        for ($i = 0; $i < 14; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setPseudo('utilisateur' . $i);
            $utilisateur->setNom('Nom' . $i);
            $utilisateur->setPrenom('Prenom' . $i);
            $utilisateur->setPassword('password' . $i);
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setDateDeNaissance(new \DateTime('now'));
            $utilisateurs->add($utilisateur);
        }

        $utilisateurs[2]->addUtilisateursInterdit($utilisateurs[3]);
        $utilisateurs[2]->addUtilisateursInterdit($utilisateurs[4]);
        $utilisateurs[7]->addUtilisateursInterdit($utilisateurs[8]);
        $utilisateurs[11]->addUtilisateursInterdit($utilisateurs[12]);

        foreach ($utilisateurs as $utilisateur) {
            $manager->persist($utilisateur);
        }


        $manager->flush();
    }
}
