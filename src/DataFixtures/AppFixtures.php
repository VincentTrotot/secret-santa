<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $utilisateurs = new ArrayCollection();
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 11; $i++) {
            $utilisateur = new Utilisateur();
            $nom = $faker->lastName;
            $prenom = $faker->firstName;
            $utilisateur->setPseudo($prenom . '.' . $nom);
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setPassword($faker->password);
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setDateDeNaissance($faker->dateTimeBetween('-45 years', '-20 years'));
            $utilisateurs->add($utilisateur);
        }

        $utilisateurs[1]->addUtilisateursInterdit($utilisateurs[2]);
        $utilisateurs[3]->addUtilisateursInterdit($utilisateurs[4]);
        $utilisateurs[5]->addUtilisateursInterdit($utilisateurs[6]);
        $utilisateurs[7]->addUtilisateursInterdit($utilisateurs[8]);
        $utilisateurs[9]->addUtilisateursInterdit($utilisateurs[10]);

        foreach ($utilisateurs as $utilisateur) {
            $manager->persist($utilisateur);
        }


        $manager->flush();
    }
}
