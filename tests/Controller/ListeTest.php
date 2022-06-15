<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Tests\CustomWebTestCase;
use App\DataFixtures\AppFixtures;
use App\Repository\SouhaitRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Response;

class ListeTest extends CustomWebTestCase
{

    public function testRoutesListes()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/listes', ['user']);
        $this->assertRoute('/listes', ['spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }


    public function testRoutesAjouterSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/listes/ajouter', ['user']);
        $this->assertRoute('/listes/ajouter', ['spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }

    public function testRoutesModifierSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->assertRoute('/listes/modifier/1', ['user']);
        $this->assertRoute('/listes/modifier/1', ['spectateur', 'participant', 'admin'], Response::HTTP_OK, false);
    }

    public function testAjoutSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $this->connectUtilisateur('role.spectateur');

        $crawler = $this->client->request('GET', '/listes/ajouter');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Ajouté en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => '1',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été ajouté.');
    }

    public function testModifierSouhaitInexistant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/150');

        $this->assertResponseRedirects('/compte');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Ce souhait n\'existe pas.');
    }

    public function testModifierSouhaitParEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);

        $id = 1;
        for ($i = 1; $i < 12; $i++) {
            if (
                $i != $souhait->getEmetteur()->getId() &&
                $i != $souhait->getDestinataire()->getId()
            ) {
                $id = $i;
                break;
            }
        }

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($id);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Vous n\'avez pas le droit de modifier ce souhait.');
    }

    public function testModifierSouhaitAcheteParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(15);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/15');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitAcheteParAcheteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(15);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getAcheteur()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/15');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitAcheteParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(15);

        $utilisateur = null;
        for ($i = 1; $i < 12; $i++) {
            if ($i != $souhait->getAcheteur()->getId()) {
                $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($i);
                if ($utilisateur->hasRole(Utilisateur::PARTICIPANT) || $utilisateur->hasRole(Utilisateur::SPECTATEUR)) {
                    break;
                }
            }
        }
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/15');


        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Seul l\'acheteur peut modifier ce souhait.');
    }

    public function testSupprimerSouhaitParEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été supprimé.');
    }

    public function testSupprimerSouhaitParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été supprimé.');
    }

    public function testSupprimerSouhaitParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $id = 1;
        for ($i = 1; $i < 12; $i++) {
            if (
                $i != $souhait->getEmetteur()->getId() &&
                $i != $souhait->getDestinataire()->getId()
            ) {
                $id = $i;
                break;
            }
        }
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($id);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Vous n\'avez pas le droit de supprimer ce souhait.');
    }

    public function testGererSouhaitViaGET()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $this->client->loginUser($utilisateur);

        $this->client->request('GET', '/listes/supprimer/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $this->client->request('GET', '/listes/acheter/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
        $this->client->request('GET', '/listes/rendre/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testMarquerSouhaitCommeAchete()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes');

        $form = $crawler->selectButton('Marquer comme acheté')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été marqué comme acheté.');
    }

    public function testRetirerMarqueAchatSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);
        $this->setClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $this->client->loginUser($utilisateur);

        $crawler = $this->client->request('GET', '/listes');

        $form = $crawler->selectButton('Enlever la marque d\'achat')->form();

        $this->client->submit($form);

        $this->assertResponseRedirects('');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div', 'La marque d\'achat a bien été enlevée.');
    }
}
