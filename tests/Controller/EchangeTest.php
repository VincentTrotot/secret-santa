<?php

namespace App\Tests\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;

class EchangeTest extends CustomWebTestCase
{

    public function testRoutes()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/echange', ['user', 'spectateur', 'admin']);
        $this->assertRoute('/echange', ['participant'], Response::HTTP_OK, false);
    }

    public function testAccepterEchangeReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(6);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('✓')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'La demande d\'échange a bien été acceptée.');
    }

    public function testRefuserEchangeReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(6);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('✖︎')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'La demande d\'échange a été refusée.');
    }

    public function testAnnulerEchangeEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('✖︎')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre demande d\'échange a été annulée.');
    }

    public function testAccepterEchangeReceveurMaisNomRecuInterdit()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(11);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/compte');
        $form = $crawler->selectButton('✓')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Il n\'est pas possible d\'accepter cette demande. Elle a été automatiqement refusée.');
    }

    public function testEchangeDemandeurMaisNomInterditPourReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(3);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/echange');
        $form = $crawler->selectButton('Demander un échange')->form([
            'echange[receveur]' => 4
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'La personne avec qui vous voulez échanger ne peut pas avoir votre tirage.');
    }

    public function testAccepterRefuserEchangeViaGET()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $this->client->loginUser($utilisateur);

        $this->client->request('GET', '/echange/accepter/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $this->client->request('GET', '/echange/refuser/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
