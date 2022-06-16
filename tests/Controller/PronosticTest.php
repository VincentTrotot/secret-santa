<?php

namespace App\Tests\Controller;

use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class PronosticTest extends CustomWebTestCase
{
    public function testPronosticRouteNotAllowed()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/pronostic', ['user']);
    }

    public function testPronosticRouteAllowed()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/pronostic', ['spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }
}
