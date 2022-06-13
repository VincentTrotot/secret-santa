<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Echange;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $utilisateurs = new ArrayCollection();
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 11; $i++) {
            $utilisateur = new Utilisateur();
            $nom = $faker->lastName();
            $prenom = $faker->firstName();
            $utilisateur->setPseudo(
                Utilisateur::remove_accents(mb_strtolower($prenom . '.' . $nom))
            );
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setPassword($this->passwordHasher->hashPassword(
                $utilisateur,
                'password'
            ));
            $utilisateur->setRoles(['ROLE_PARTICIPANT']);
            $utilisateur->setDateDeNaissance($faker->dateTimeBetween('-45 years', '-20 years'));
            $utilisateurs->add($utilisateur);
        }

        $utilisateurs[1]->addUtilisateursInterdit($utilisateurs[2]);
        $utilisateurs[3]->addUtilisateursInterdit($utilisateurs[4]);
        $utilisateurs[5]->addUtilisateursInterdit($utilisateurs[6]);
        $utilisateurs[7]->addUtilisateursInterdit($utilisateurs[8]);
        $utilisateurs[9]->addUtilisateursInterdit($utilisateurs[10]);

        $utilisateurs[0]->setUtilisateurTire($utilisateurs[1]);
        $utilisateurs[1]->setUtilisateurTire($utilisateurs[3]);
        $utilisateurs[2]->setUtilisateurTire($utilisateurs[4]);
        $utilisateurs[3]->setUtilisateurTire($utilisateurs[5]);
        $utilisateurs[4]->setUtilisateurTire($utilisateurs[6]);
        $utilisateurs[5]->setUtilisateurTire($utilisateurs[7]);
        $utilisateurs[6]->setUtilisateurTire($utilisateurs[8]);
        $utilisateurs[7]->setUtilisateurTire($utilisateurs[9]);
        $utilisateurs[8]->setUtilisateurTire($utilisateurs[10]);
        $utilisateurs[9]->setUtilisateurTire($utilisateurs[0]);
        $utilisateurs[10]->setUtilisateurTire($utilisateurs[2]);

        $echange = new Echange();
        $echange->setDate(new \DateTime());
        $echange->setStatus(Echange::STATUS_EN_ATTENTE);
        $echange->setDemandeur($utilisateurs[0]);
        $echange->setReceveur($utilisateurs[5]);

        $manager->persist($echange);


        foreach ($utilisateurs as $utilisateur) {
            $manager->persist($utilisateur);
        }

        for ($i = 0; $i < 3; $i++) {
            $utilisateur = new Utilisateur();
            $nom = $faker->lastName();
            $prenom = $faker->firstName();
            $utilisateur->setPseudo(
                Utilisateur::remove_accents(mb_strtolower($prenom . '.' . $nom))
            );
            $utilisateur->setNom($nom);
            $utilisateur->setPrenom($prenom);
            $utilisateur->setPassword($this->passwordHasher->hashPassword(
                $utilisateur,
                'password'
            ));
            $utilisateur->setRoles(['ROLE_USER']);
            $utilisateur->setDateDeNaissance($faker->dateTimeBetween('-45 years', '-20 years'));
            $utilisateurs->add($utilisateur);
        }

        foreach ($utilisateurs as $utilisateur) {
            $manager->persist($utilisateur);
        }


        $manager->flush();
    }
}
