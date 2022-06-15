<?php

namespace App\Tests\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class TirageTest extends CustomWebTestCase
{

    public function testTirageRoutes()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/tirage', ['user']);
        $this->assertRoute('/tirage', ['spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }
}
