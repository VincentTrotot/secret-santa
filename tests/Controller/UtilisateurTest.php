<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class UtilisateurTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRootCompteRedirect()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/compte');

        $this->assertResponseRedirects('/se-connecter');
    }

    public function testRootCompteForUsers()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/compte');

        $this->assertSelectorTextContains('h1', 'role.spectateur');
    }
}
