<?php

namespace App\Test\Controller;

use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class SecurityTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testLoginPage()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/se-connecter');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Se connecter');
    }

    public function testLoginWithBadCredentials()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/se-connecter');
        $form = $crawler->selectButton('Se connecter')->form([
            'pseudo' => 'toto',
            'password' => 'toto',
        ]);
        $this->assertSelectorNotExists('div.alert-danger');
        $client->submit($form);
        $this->assertResponseRedirects('/se-connecter');
        $client->followRedirect();
        $this->assertSelectorExists('div.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $crawler = $client->request('GET', '/se-connecter');
        $form = $crawler->selectButton('Se connecter')->form([
            'pseudo' => 'jean.dupont',
            'password' => 'password',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/compte');
        $client->followRedirect();
        $this->assertSelectorExists('h1', 'jean.dupont');
    }
}
