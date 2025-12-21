<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215150253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor user roles from JSON column to proper many-to-many relationship';
    }

    public function up(Schema $schema): void
    {
        // Create roles table
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create user_roles junction table
        $this->addSql('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_54FCD59FA76ED395 (user_id), INDEX IDX_54FCD59FD60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        
        // Insert default roles
        $this->addSql("INSERT INTO roles (name, description, created_at) VALUES ('ROLE_USER', 'Utilisateur standard', NOW())");
        $this->addSql("INSERT INTO roles (name, description, created_at) VALUES ('ROLE_MODERATOR', 'Modérateur', NOW())");
        $this->addSql("INSERT INTO roles (name, description, created_at) VALUES ('ROLE_ADMIN', 'Administrateur', NOW())");
        $this->addSql("INSERT INTO roles (name, description, created_at) VALUES ('ROLE_SUPER_ADMIN', 'Super Administrateur', NOW())");
        
        // Migrate existing user roles from JSON to the new structure
        // First, get all users with their roles from the JSON column
        $this->addSql("
            INSERT INTO user_roles (user_id, role_id)
            SELECT u.id, r.id
            FROM users u
            JOIN roles r ON JSON_CONTAINS(u.roles, CONCAT('\"', r.name, '\"'))
        ");
        
        // Drop the old roles column
        $this->addSql('ALTER TABLE users DROP roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD60322AC');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL');
    }
}
