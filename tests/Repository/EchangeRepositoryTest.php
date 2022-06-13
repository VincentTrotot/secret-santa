<?php

namespace App\Test\Repository;

use App\DataFixtures\AppFixtures;
use App\Repository\EchangeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class EchangeRepositoryTest extends KernelTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testFindForUtilisateur()
    {
        self::bootKernel();
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        $echange = static::getContainer()->get(EchangeRepository::class)->findForUtilisateur(1);
        $this->assertEquals(1, count($echange));

        $echange = static::getContainer()->get(EchangeRepository::class)->findForUtilisateur(6);
        $this->assertEquals(1, count($echange));
    }
}
