<?php

namespace App\Tests\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class UtilisateurTest extends CustomWebTestCase
{

    public function testRoutes()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/compte', []);
        $this->assertRoute('/compte', ['user', 'spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }
}
