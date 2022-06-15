<?php

namespace App\Tests;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class CustomWebTestCase extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var KernelBrowser */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function setClient()
    {
        self::ensureKernelShutdown();
        $this->client = self::createClient();
    }

    protected function connectUtilisateur($pseudo)
    {
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => $pseudo]);
        $this->client->loginUser($utilisateur);
    }

    protected function assertRoute($route, array $pseudos, $statusCode = Response::HTTP_FORBIDDEN, $anonymousRedirect = true, $method = 'GET')
    {
        if ($anonymousRedirect) {
            $this->client->request($method, $route);
            $this->assertResponseRedirects('/se-connecter');
        }
        if (!empty($pseudos)) {
            foreach ($pseudos as $pseudo) {
                $this->connectUtilisateur('role.' . $pseudo);
                $this->client->request($method, $route);
                $this->assertResponseStatusCodeSame($statusCode);
            }
        }
    }
}
