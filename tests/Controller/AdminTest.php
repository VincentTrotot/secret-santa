<?php

namespace App\Tests\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class AdminTest extends CustomWebTestCase
{

    public function testAdminTirageRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/tirage', ['user', 'spectateur', 'participant']);
    }

    public function testAdminTirageRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/tirage', ['admin'], Response::HTTP_OK, false);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminTirageRevealRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/tirage/reveal', ['user', 'spectateur', 'participant']);
    }

    public function testAdminTirageRevealRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/tirage/reveal', ['admin'], Response::HTTP_OK, false);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminTirageMakeRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/tirage/make', ['user', 'spectateur', 'participant']);
    }

    public function testAdminTirageMakeRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->connectUtilisateur('role.admin');
        $this->client->request('GET', '/admin/tirage/make');
        $this->assertResponseRedirects('/admin/tirage');
    }

    public function testAdminTirageResetRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        $this->setClient();
        $this->assertRoute('/admin/tirage/reset', ['user', 'spectateur', 'participant']);
    }

    public function testAdminTirageResetRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');
        $this->client->request('GET', '/admin/tirage/reset');
        $this->assertResponseRedirects('/admin/tirage');
    }

    public function testAdminUtilisateursRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/admin/utilisateurs', ['user', 'spectateur', 'participant']);
    }

    public function testAdminUtilisateursRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');
        $this->client->request('GET', '/admin/utilisateurs');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('table.table.table-sm');
    }

    public function testAdminModifierUtilisateurRouteRoleNotAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->assertRoute('/admin/utilisateur/modifier/1', ['user', 'spectateur', 'participant']);
    }

    public function testAdminModifierUtilisateurRouteRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();
        $this->connectUtilisateur('role.admin');

        $this->client->request('GET', '/admin/utilisateur/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Modifier un utilisateur');
    }

    public function testAdminModifierUtilisateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');


        $crawler = $this->client->request('GET', '/admin/utilisateur/modifier/1');

        $form = $crawler->selectButton('Modifier l\'utilisateur')->form([
            "utilisateur[prenom]" => "Prenom",
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'L\'utilisateur a été modifié.');
    }

    public function testAdminModifierUtilisateurNonExistant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');

        $crawler = $this->client->request('GET', '/admin/utilisateur/modifier/150');

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Cet utilisateur n\'existe pas.');
    }

    public function testAdminSupprimerUtilisateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');

        $crawler = $this->client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Supprimer l\'utilisateur')->form([]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'L\'utilisateur a bien été supprimé.');
    }

    public function testAdminActiverRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');

        $crawler = $this->client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Spectateur')->form([]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }

    public function testAdminActiverRoleParticipant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');


        $crawler = $this->client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Participant')->form([]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }

    public function testAdminActiverRoleNotActive()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.admin');

        $crawler = $this->client->request('GET', '/admin/utilisateurs');

        $form = $crawler->selectButton('Désactiver le compte')->form([]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le role a bien été ajouté.');
    }
}
