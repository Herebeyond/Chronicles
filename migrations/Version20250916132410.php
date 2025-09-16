<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916132410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characters (id INT AUTO_INCREMENT NOT NULL, species_id INT NOT NULL, race_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, gender VARCHAR(100) DEFAULT NULL, age INT DEFAULT NULL, birthplace VARCHAR(255) DEFAULT NULL, occupation VARCHAR(255) DEFAULT NULL, traits JSON DEFAULT NULL, background LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3A29410EB2A1D860 (species_id), INDEX IDX_3A29410E6E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE races (id INT AUTO_INCREMENT NOT NULL, species_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5DBD1EC9B2A1D860 (species_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410EB2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE characters ADD CONSTRAINT FK_3A29410E6E59D40D FOREIGN KEY (race_id) REFERENCES races (id)');
        $this->addSql('ALTER TABLE races ADD CONSTRAINT FK_5DBD1EC9B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE characters DROP FOREIGN KEY FK_3A29410EB2A1D860');
        $this->addSql('ALTER TABLE characters DROP FOREIGN KEY FK_3A29410E6E59D40D');
        $this->addSql('ALTER TABLE races DROP FOREIGN KEY FK_5DBD1EC9B2A1D860');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE races');
        $this->addSql('DROP TABLE species');
    }
}
