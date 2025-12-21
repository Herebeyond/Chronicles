<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919113508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates users table and adds default admin user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(100) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add default admin user (username: Nox, existing user from database)
        // This ensures there's always an admin account available after database reset
        // Note: Roles will be assigned in a later migration via the roles table
        $this->addSql("INSERT INTO users (email, username, first_name, last_name, roles, password, is_active, created_at) VALUES ('baillard.bjm2@orange.fr', 'Nox', NULL, NULL, '[\"ROLE_SUPER_ADMIN\",\"ROLE_ADMIN\",\"ROLE_USER\"]', '\$2y\$13\$MfbGIfHpsemicl9l4.jfk.LKKl7GRzzhW4UIYoT5yguCy3pWHvEBK', 1, NOW())");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
