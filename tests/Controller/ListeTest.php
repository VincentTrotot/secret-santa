<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\DataFixtures\AppFixtures;
use App\Repository\SouhaitRepository;
use App\Repository\UtilisateurRepository;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ListeTest extends WebTestCase
{

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testRouteListesRedirects()
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        $crawler = $client->request('GET', '/listes');

        $this->assertResponseRedirects('/se-connecter');
    }

    public function testRouteListesRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteListesRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $this->assertSelectorTextContains('a.btn.btn-outline-success', 'Ajouter un souhait');
    }

    public function testRouteListesRoleParticipant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.participant']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $this->assertSelectorTextContains('a.btn.btn-outline-success', 'Ajouter un souhait');
    }

    public function testRouteListesRoleAdmin()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.admin']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $this->assertSelectorTextContains('a.btn.btn-outline-success', 'Ajouter un souhait');
    }


    public function testRouteAjouterSouhaitRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/ajouter');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteAjouterSouhaitRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/ajouter');

        $this->assertSelectorTextContains('h1', 'Ajouter un souhait');
    }

    public function testRouteModifierSouhaitRoleUser()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.user']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRouteModifierSouhaitRoleSpectateur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');
        $this->assertSelectorTextContains('h1', 'Modifier un souhait');
    }

    public function testAjoutSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->findOneBy(['pseudo' => 'role.spectateur']);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/ajouter');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Ajouté en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => '1',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été ajouté.');
    }

    public function testModifierSouhaitInexistant()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/150');

        $this->assertResponseRedirects('/compte');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Ce souhait n\'existe pas.');
    }

    public function testModifierSouhaitParEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

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
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Vous n\'avez pas le droit de modifier ce souhait.');
    }

    public function testModifierSouhaitAcheteParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(15);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/15');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitAcheteParAcheteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(15);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getAcheteur()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/15');

        $form = $crawler->selectButton('Enregistrer')->form([
            'souhait[nom]' => 'Unit',
            'souhait[informations]' => 'Modifié en test fonctionnel',
            'souhait[lien]' => '',
            'souhait[destinataire]' => $souhait->getDestinataire()->getId(),
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Le souhait a bien été modifié.');
    }

    public function testModifierSouhaitAcheteParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

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
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/15');


        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Seul l\'acheteur peut modifier ce souhait.');
    }

    public function testSupprimerSouhaitParEmetteur()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été supprimé.');
    }

    public function testSupprimerSouhaitParDestinataire()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getDestinataire()->getId());
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été supprimé.');
    }

    public function testSupprimerSouhaitParNonConcerne()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

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
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes/modifier/1');

        $form = $crawler->selectButton('Supprimer')->form();

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Vous n\'avez pas le droit de supprimer ce souhait.');
    }

    public function testSupprimerSouhaitViaGET()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $souhait = self::getContainer()->get(SouhaitRepository::class)->find(1);
        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find($souhait->getEmetteur()->getId());
        $client->loginUser($utilisateur);

        $client->request('GET', '/listes/supprimer/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testMarquerSouhaitCommeAchete()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $form = $crawler->selectButton('Marquer comme acheté')->form();

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'Votre souhait a bien été marqué comme acheté.');
    }

    public function testRetirerMarqueAchatSouhait()
    {
        $this->databaseTool->loadFixtures([AppFixtures::class]);

        self::ensureKernelShutdown();
        $client = self::createClient();

        $utilisateur = self::getContainer()->get(UtilisateurRepository::class)->find(1);
        $client->loginUser($utilisateur);

        $crawler = $client->request('GET', '/listes');

        $form = $crawler->selectButton('Enlever la marque d\'achat')->form();

        $client->submit($form);

        $this->assertResponseRedirects('');
        $client->followRedirect();
        $this->assertSelectorTextContains('div', 'La marque d\'achat a bien été enlevée.');
    }
}
