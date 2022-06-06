<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220605114817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE souhait (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, achete TINYINT(1) NOT NULL, INDEX IDX_DCACDCC8FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, utilisateur_tire_id INT DEFAULT NULL, pseudo VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_de_naissance DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D1C63B386CC499D (pseudo), UNIQUE INDEX UNIQ_1D1C63B3C6512AFA (utilisateur_tire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_utilisateur (utilisateur_source INT NOT NULL, utilisateur_target INT NOT NULL, INDEX IDX_E9FA6E203E2745F8 (utilisateur_source), INDEX IDX_E9FA6E2027C21577 (utilisateur_target), PRIMARY KEY(utilisateur_source, utilisateur_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur_interdit (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE souhait ADD CONSTRAINT FK_DCACDCC8FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3C6512AFA FOREIGN KEY (utilisateur_tire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur_utilisateur ADD CONSTRAINT FK_E9FA6E203E2745F8 FOREIGN KEY (utilisateur_source) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur_utilisateur ADD CONSTRAINT FK_E9FA6E2027C21577 FOREIGN KEY (utilisateur_target) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE souhait DROP FOREIGN KEY FK_DCACDCC8FB88E14F');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3C6512AFA');
        $this->addSql('ALTER TABLE utilisateur_utilisateur DROP FOREIGN KEY FK_E9FA6E203E2745F8');
        $this->addSql('ALTER TABLE utilisateur_utilisateur DROP FOREIGN KEY FK_E9FA6E2027C21577');
        $this->addSql('DROP TABLE souhait');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE utilisateur_utilisateur');
        $this->addSql('DROP TABLE utilisateur_interdit');
    }
}
