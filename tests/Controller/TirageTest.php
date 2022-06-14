<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class TirageTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testTirageRoutes()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/tirage');
        $this->assertResponseRedirects('/se-connecter');

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);
        $crawler = $client->request('GET', '/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);
        $crawler = $client->request('GET', '/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);
        $crawler = $client->request('GET', '/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);
        $crawler = $client->request('GET', '/tirage');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
