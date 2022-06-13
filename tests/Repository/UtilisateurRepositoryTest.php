<?php

namespace App\Test\Repository;

use DateTime;
use App\Entity\Utilisateur;
use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use function PHPUnit\Framework\assertEquals;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;

class UtilisateurRepositoryTest extends KernelTestCase
{

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testFindAllUtilisateurs()
    {
        self::bootKernel();
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $utilisateurs = static::getContainer()->get(UtilisateurRepository::class)->findAll();
        assertEquals(14, count($utilisateurs));
    }

    public function testFindAllParticipants()
    {
        self::bootKernel();
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $utilisateurs = static::getContainer()->get(UtilisateurRepository::class)->findAllParticipants();
        foreach ($utilisateurs as $utilisateur) {
            $this->assertNotEquals($utilisateur, Utilisateur::PARTICIPANT);
        }
        assertEquals(11, count($utilisateurs));
    }

    public function testNoPseudoUtilisateur()
    {
        self::bootKernel();
        $utilisateur = new Utilisateur();
        $utilisateur->setNom('Nom');
        $utilisateur->setPrenom('Prenom');
        $utilisateur->setPassword('password');

        $this->expectException(NotNullConstraintViolationException::class);
        static::getContainer()->get(UtilisateurRepository::class)->add($utilisateur, true);
    }

    public function testFindAllWithThatIdFirst()
    {
        self::bootKernel();
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $utilisateur = static::getContainer()->get(UtilisateurRepository::class)->find(1);
        $utilisateurs = static::getContainer()->get(UtilisateurRepository::class)->findAllWithThatIdFirst($utilisateur->getId());

        $this->assertEquals(1, $utilisateurs[0]->getId());
    }
}
