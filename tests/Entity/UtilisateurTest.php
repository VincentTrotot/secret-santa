<?php

namespace App\Tests\Entity;

use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;
use App\DataFixtures\AppFixtures;
use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class UtilisateurTest extends TestCase
{


    public function testAddRoleAndRemoveRole()
    {

        $utilisateur = new Utilisateur();

        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->addRole(Utilisateur::SPECTATEUR);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertTrue(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->addRole(Utilisateur::PARTICIPANT);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertTrue(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->addRole(Utilisateur::ADMIN);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertTrue(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertTrue(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->removeRole(Utilisateur::ADMIN);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertTrue(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->removeRole(Utilisateur::PARTICIPANT);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));

        $utilisateur->removeRole(Utilisateur::SPECTATEUR);
        $this->assertTrue(in_array(Utilisateur::USER, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::SPECTATEUR, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
        $this->assertFalse(in_array(Utilisateur::ADMIN, $utilisateur->getRoles()));
    }

    public function testToggleRole()
    {
        $utilisateur = new Utilisateur();
        $utilisateur->addRole(Utilisateur::PARTICIPANT);
        $this->assertTrue(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));

        $utilisateur->toggleRole(Utilisateur::PARTICIPANT);
        $this->assertFalse(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));

        $utilisateur->toggleRole(Utilisateur::PARTICIPANT);
        $this->assertTrue(in_array(Utilisateur::PARTICIPANT, $utilisateur->getRoles()));
    }

    public function testGetUtilisateursInterditsContientUtilisateur()
    {
        $utilisateur = new Utilisateur();
        $this->assertTrue($utilisateur->getUtilisateursInterdits()->contains($utilisateur));
    }

    public function testIsTiragePossible()
    {

        $utilisateurs = new ArrayCollection();
        for ($i = 0; $i < 10; $i++) {
            $utilisateurs->add($this->getUtilisateur($i));
        }
        $utilisateurs[0]->addUtilisateursInterdit($utilisateurs[1]);
        $utilisateurs[0]->addUtilisateursInterdit($utilisateurs[2]);

        $this->assertTrue($utilisateurs[0]->isTiragePossible($utilisateurs));
        $this->assertTrue($utilisateurs[0]->isTiragePossible(
            new ArrayCollection(
                [
                    $utilisateurs[0],
                    $utilisateurs[1],
                    $utilisateurs[2],
                    $utilisateurs[3],
                    $utilisateurs[4],
                ]
            )
        ));
        $this->assertTrue($utilisateurs[0]->isTiragePossible(
            new ArrayCollection(
                [
                    $utilisateurs[0],
                    $utilisateurs[1],
                    $utilisateurs[2],
                    $utilisateurs[3],
                ]
            )
        ));
        $this->assertFalse($utilisateurs[0]->isTiragePossible(
            new ArrayCollection(
                [
                    $utilisateurs[0],
                    $utilisateurs[1],
                    $utilisateurs[2],
                ]
            )
        ));
    }

    public function testTire()
    {
        $utilisateurs = new ArrayCollection();
        for ($i = 0; $i < 3; $i++) {
            $utilisateurs->add($this->getUtilisateur($i));
        }
        $utilisateurs[0]->addUtilisateursInterdit($utilisateurs[1]);

        $this->assertEquals(2, $utilisateurs[0]->tire($utilisateurs)->getId());
    }


    private function getUtilisateur($id)
    {
        $utilisateur = $this->getMockBuilder(Utilisateur::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $utilisateur->method('getId')->willReturn($id);


        return $utilisateur;
    }
}
