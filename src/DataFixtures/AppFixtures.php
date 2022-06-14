<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Echange;
use App\Entity\Souhait;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $utilisateurs;
    private $faker;
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->seed(9025);
        $this->utilisateurs = new ArrayCollection();
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUtilisateurs($manager);
        $this->createEchanges($manager);
        $this->createSouhaits($manager);
    }

    private function createUtilisateurs(ObjectManager $manager)
    {
        // 10 utilisateurs aléatoires  + 1 non aléatoire -> participants
        for ($i = 0; $i < 10; $i++) {
            $utilisateur = new Utilisateur();
            $nom = $this->faker->lastName();
            $prenom = $this->faker->firstName();
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
            $utilisateur->setDateDeNaissance($this->faker->dateTimeBetween('-45 years', '-20 years'));
            $this->utilisateurs->add($utilisateur);
        }

        $participant = new Utilisateur();
        $nom = 'Participant';
        $prenom = 'Role';
        $participant->setPseudo(
            Utilisateur::remove_accents(mb_strtolower($prenom . '.' . $nom))
        );
        $participant->setNom($nom);
        $participant->setPrenom($prenom);
        $participant->setPassword($this->passwordHasher->hashPassword(
            $participant,
            'password'
        ));
        $participant->setRoles(['ROLE_PARTICIPANT']);
        $participant->setDateDeNaissance($this->faker->dateTimeBetween('-45 years', '-20 years'));
        $this->utilisateurs->add($participant);


        // Ajout des interdits
        $this->utilisateurs[1]->addUtilisateursInterdit($this->utilisateurs[2]);
        $this->utilisateurs[2]->addUtilisateursInterdit($this->utilisateurs[1]);
        $this->utilisateurs[3]->addUtilisateursInterdit($this->utilisateurs[4]);
        $this->utilisateurs[4]->addUtilisateursInterdit($this->utilisateurs[3]);
        $this->utilisateurs[5]->addUtilisateursInterdit($this->utilisateurs[6]);
        $this->utilisateurs[6]->addUtilisateursInterdit($this->utilisateurs[5]);
        $this->utilisateurs[7]->addUtilisateursInterdit($this->utilisateurs[8]);
        $this->utilisateurs[8]->addUtilisateursInterdit($this->utilisateurs[7]);
        $this->utilisateurs[9]->addUtilisateursInterdit($this->utilisateurs[10]);
        $this->utilisateurs[10]->addUtilisateursInterdit($this->utilisateurs[9]);

        // Ajout du tirage
        $this->utilisateurs[0]->setUtilisateurTire($this->utilisateurs[1]);
        $this->utilisateurs[1]->setUtilisateurTire($this->utilisateurs[3]);
        $this->utilisateurs[2]->setUtilisateurTire($this->utilisateurs[4]);
        $this->utilisateurs[3]->setUtilisateurTire($this->utilisateurs[5]);
        $this->utilisateurs[4]->setUtilisateurTire($this->utilisateurs[6]);
        $this->utilisateurs[5]->setUtilisateurTire($this->utilisateurs[7]);
        $this->utilisateurs[6]->setUtilisateurTire($this->utilisateurs[8]);
        $this->utilisateurs[7]->setUtilisateurTire($this->utilisateurs[9]);
        $this->utilisateurs[8]->setUtilisateurTire($this->utilisateurs[10]);
        $this->utilisateurs[9]->setUtilisateurTire($this->utilisateurs[0]);
        $this->utilisateurs[10]->setUtilisateurTire($this->utilisateurs[2]);


        foreach ($this->utilisateurs as $utilisateur) {
            $manager->persist($utilisateur);
        }

        // Création d'un utilisateur -> ROLE_USER
        $utilisateur = new Utilisateur();
        $nom = 'User';
        $prenom = 'Role';
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
        $utilisateur->setDateDeNaissance($this->faker->dateTimeBetween('-45 years', '-20 years'));
        $manager->persist($utilisateur);

        // Création d'un utilisateur -> ROLE_SPECTATEUR
        $utilisateur = new Utilisateur();
        $nom = 'Spectateur';
        $prenom = 'Role';
        $utilisateur->setPseudo(
            Utilisateur::remove_accents(mb_strtolower($prenom . '.' . $nom))
        );
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setPassword($this->passwordHasher->hashPassword(
            $utilisateur,
            'password'
        ));
        $utilisateur->setRoles(['ROLE_SPECTATEUR']);
        $utilisateur->setDateDeNaissance($this->faker->dateTimeBetween('-45 years', '-20 years'));
        $manager->persist($utilisateur);


        // Création d'un utilisateur -> ROLE_ADMIN
        $utilisateur = new Utilisateur();
        $nom = 'Admin';
        $prenom = 'Role';
        $utilisateur->setPseudo(
            Utilisateur::remove_accents(mb_strtolower($prenom . '.' . $nom))
        );
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setPassword($this->passwordHasher->hashPassword(
            $utilisateur,
            'password'
        ));
        $utilisateur->setRoles(['ROLE_ADMIN']);
        $utilisateur->setDateDeNaissance($this->faker->dateTimeBetween('-45 years', '-20 years'));
        $manager->persist($utilisateur);

        $manager->persist($utilisateur);

        $manager->flush();
    }

    private function createEchanges(ObjectManager $manager)
    {
        // Création d'un échange
        $echange = new Echange();
        $echange->setDate(new \DateTime());
        $echange->setStatus(Echange::STATUS_EN_ATTENTE);
        $echange->setDemandeur($this->utilisateurs[0]);
        $echange->setReceveur($this->utilisateurs[5]);

        $manager->persist($echange);

        $echange = new Echange();
        $echange->setDate(new \DateTime());
        $echange->setStatus(Echange::STATUS_EN_ATTENTE);
        $echange->setDemandeur($this->utilisateurs[1]);
        $echange->setReceveur($this->utilisateurs[10]);

        $manager->persist($echange);
        $manager->flush();
    }

    private function createSouhaits(ObjectManager $manager)
    {
        // Création de souhaits non achetés
        for ($i = 0; $i < 10; $i++) {
            $souhait = new Souhait();
            $souhait->setCreatedAt(new \DateTimeImmutable());
            $souhait->setEmetteur($this->utilisateurs[rand(0, 10)]);
            $souhait->setDestinataire($this->utilisateurs[rand(0, 10)]);
            $souhait->setNom($this->faker->word());
            $souhait->setInformations($this->faker->sentence());
            $souhait->setAchete(false);

            $manager->persist($souhait);
        }


        // Création de souhaits achetés
        for ($i = 0; $i < 5; $i++) {
            $souhait = new Souhait();
            $souhait->setCreatedAt(new \DateTimeImmutable());
            $souhait->setEmetteur($this->utilisateurs[rand(0, 10)]);
            $souhait->setDestinataire($this->utilisateurs[rand(0, 10)]);
            $souhait->setNom($this->faker->word());
            $souhait->setInformations($this->faker->sentence());
            $souhait->setAchete(true);
            do {
                $done = false;
                $acheteur = rand(0, 10);
                if ($acheteur !== $souhait->getDestinataire()->getId()) {
                    $souhait->setAcheteur($this->utilisateurs[$acheteur]);
                    $done = true;
                }
            } while (!$done);

            $manager->persist($souhait);
        }
        $souhait = new Souhait();
        $souhait->setCreatedAt(new \DateTimeImmutable());
        $souhait->setEmetteur($this->utilisateurs[rand(0, 10)]);
        $souhait->setDestinataire($this->utilisateurs[rand(0, 10)]);
        $souhait->setNom($this->faker->word());
        $souhait->setInformations($this->faker->sentence());
        $souhait->setAchete(true);
        $souhait->setAcheteur($this->utilisateurs[0]);

        $manager->persist($souhait);

        $manager->flush();
    }
}
