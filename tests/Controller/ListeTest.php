<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class ListeTest extends WebTestCase
{

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRouteListesRedirects()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/listes');

        $this->assertResponseRedirects('/se-connecter');
    }

    public function testRouteListesRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteListesRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $this->assertSelectorTextContains('a.btn.btn-outline-success', 'Ajouter un souhait');
    }


    public function testRouteAjouterSouhaitRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/ajouter');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteAjouterSouhaitRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/ajouter');

        $this->assertSelectorTextContains('h1', 'Ajouter un souhait');
    }

    public function testRouteModifierSouhaitRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteModifierSouhaitRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');
        $this->assertSelectorTextContains('h1', 'Modifier un souhait');
    }
}
