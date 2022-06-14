<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class EchangeTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRootRoleAnonyme()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/echange');

        $this->assertResponseRedirects('/se-connecter');
    }

    public function testRootRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/echange');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRootRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/echange');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRootRoleParticipant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/echange');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testRootRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/echange');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAccepterEchangeReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(6);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/compte');
        $form = $crawler->selectButton('✓')->form();
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'La demande d\'échange a bien été acceptée.');
    }

    public function testRefuserEchangeReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(6);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/compte');
        $form = $crawler->selectButton('✖︎')->form();
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'La demande d\'échange a été refusée.');
    }

    public function testAnnulerEchangeEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/compte');
        $form = $crawler->selectButton('✖︎')->form();
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre demande d\'échange a été annulée.');
    }

    public function testAccepterEchangeReceveurMaisNomRecuInterdit()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(11);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/compte');
        $form = $crawler->selectButton('✓')->form();
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Il n\'est pas possible d\'accepter cette demande. Elle a été automatiqement refusée.');
    }

    public function testEchangeDemanveurMaisNomInterditPourReceveur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(3);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/echange');
        $form = $crawler->selectButton('Demander un échange')->form([
            'echange[receveur]' => 4
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'La personne avec qui vous voulez échanger ne peut pas avoir votre tirage.');
    }

    public function testAccepterRefuserEchangeViaGET()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $client->loginUser($utilisateur);

        $client->request('GET', '/echange/accepter/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $client->request('GET', '/echange/refuser/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
