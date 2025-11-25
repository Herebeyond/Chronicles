<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027112439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE idea_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, is_default TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_E4FA1F8F5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ideas (id_idea INT AUTO_INCREMENT NOT NULL, parent_idea_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, category VARCHAR(100) NOT NULL, certainty_level VARCHAR(50) NOT NULL, status VARCHAR(50) DEFAULT NULL, tags JSON DEFAULT NULL, comments LONGTEXT DEFAULT NULL, inspiration_source VARCHAR(255) DEFAULT NULL, priority INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1DB2F1DE29279B3B (parent_idea_id), PRIMARY KEY(id_idea)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ideas ADD CONSTRAINT FK_1DB2F1DE29279B3B FOREIGN KEY (parent_idea_id) REFERENCES ideas (id_idea) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ideas DROP FOREIGN KEY FK_1DB2F1DE29279B3B');
        $this->addSql('DROP TABLE idea_categories');
        $this->addSql('DROP TABLE ideas');
    }
}
