<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class AdminTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testAdminTirageRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/tirage');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminTirageRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminTirageRevealRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/tirage/reveal');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reveal');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reveal');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reveal');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminTirageRevealRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reveal');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminTirageMakeRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/tirage/make');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/make');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/make');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/make');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminTirageMakeRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/make');
        $this->assertResponseRedirects('/admin/tirage');
    }

    public function testAdminTirageResetRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/tirage/reset');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reset');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reset');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reset');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminTirageResetRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/tirage/reset');
        $this->assertResponseRedirects('/admin/tirage');
    }

    public function testAdminUtilisateursRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminUtilisateursRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminModifierUtilisateurRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        // Anonyme
        $client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseRedirects('/se-connecter');

        // User
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // spectateur
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // participant
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminModifierUtilisateurRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Modifier un utilisateur');
    }

    public function testAdminModifierUtilisateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateur/modifier/1');

        $form = $crawler->selectButton('Modifier l\'utilisateur')->form([
            "utilisateur[prenom]" => "Prenom",
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'L\'utilisateur a été modifié.');
    }

    public function testAdminModifierUtilisateurNonExistant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateur/modifier/150');

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Cet utilisateur n\'existe pas.');
    }

    public function testAdminSupprimerUtilisateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Supprimer l\'utilisateur')->form([]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'L\'utilisateur a bien été supprimé.');
    }

    public function testAdminActiverRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Spectateur')->form([]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }

    public function testAdminActiverRoleParticipant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Participant')->form([]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }

    public function testAdminActiverRoleNotActive()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();


        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Désactiver le compte')->form([]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }
}
