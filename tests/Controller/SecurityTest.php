<?php

namespace App\Test\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class SecurityTest extends CustomWebTestCase
{

    public function testLoginPage()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $crawler = $this->client->request('GET', '/se-connecter');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Se connecter');
    }

    public function testLoginWithBadCredentials()
    {
        $this->setClient();

        $crawler = $this->client->request('GET', '/se-connecter');
        $form = $crawler->selectButton('Se connecter')->form([
            'pseudo' => 'toto',
            'password' => 'toto',
        ]);
        $this->assertSelectorNotExists('div.alert-danger');
        $this->client->submit($form);
        $this->assertResponseRedirects('/se-connecter');
        $this->client->followRedirect();
        $this->assertSelectorExists('div.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        $this->setClient();

        $crawler = $this->client->request('GET', '/se-connecter');
        $form = $crawler->selectButton('Se connecter')->form([
            'pseudo' => 'role.spectateur',
            'password' => 'password',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/compte');
        $this->client->followRedirect();
        $this->assertSelectorExists('h1', 'role.spectateur');
    }
}
