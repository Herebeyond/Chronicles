<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Interactive Map System
 * Creates tables for maps, interest points, and point types
 */
final class Version20260118225509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables for interactive map system with interest points and point types';
    }

    public function up(Schema $schema): void
    {
        // Create tables
        $this->addSql('CREATE TABLE interest_point_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) NOT NULL, icon VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE interest_points (id INT AUTO_INCREMENT NOT NULL, map_id INT NOT NULL, type_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, x_coordinate NUMERIC(10, 6) NOT NULL, y_coordinate NUMERIC(10, 6) NOT NULL, other_names VARCHAR(255) DEFAULT NULL, main_image VARCHAR(255) DEFAULT NULL, gallery JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5D34746053C55F64 (map_id), INDEX IDX_5D347460C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maps (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image_file VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE interest_points ADD CONSTRAINT FK_5D34746053C55F64 FOREIGN KEY (map_id) REFERENCES maps (id)');
        $this->addSql('ALTER TABLE interest_points ADD CONSTRAINT FK_5D347460C54C8C93 FOREIGN KEY (type_id) REFERENCES interest_point_types (id)');
        
        // Insert default point types with colors
        $now = date('Y-m-d H:i:s');
        $this->addSql("INSERT INTO interest_point_types (name, color, icon, created_at) VALUES 
            ('Cité', '#4a90d9', '🏰', '$now'),
            ('Donjon', '#8b4513', '⚔️', '$now'),
            ('Temple', '#9b59b6', '🏛️', '$now'),
            ('Montagne', '#7f8c8d', '⛰️', '$now'),
            ('Forêt', '#27ae60', '🌲', '$now'),
            ('Rivière', '#3498db', '🌊', '$now'),
            ('Château', '#c0392b', '🏰', '$now'),
            ('Caverne', '#34495e', '🕳️', '$now'),
            ('Lieu', '#e67e22', '📍', '$now'),
            ('Village', '#f39c12', '🏘️', '$now'),
            ('Tour', '#1abc9c', '🗼', '$now'),
            ('Ruines', '#95a5a6', '🏚️', '$now'),
            ('Port', '#2980b9', '⚓', '$now'),
            ('Pont', '#d35400', '🌉', '$now'),
            ('Mine', '#7f8c8d', '⛏️', '$now')
        ");
        
        // Insert a default map
        $this->addSql("INSERT INTO maps (name, description, created_at) VALUES 
            ('Carte du Monde', 'Carte principale des Mondes Oubliés', '$now')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interest_points DROP FOREIGN KEY FK_5D34746053C55F64');
        $this->addSql('ALTER TABLE interest_points DROP FOREIGN KEY FK_5D347460C54C8C93');
        $this->addSql('DROP TABLE interest_point_types');
        $this->addSql('DROP TABLE interest_points');
        $this->addSql('DROP TABLE maps');
    }
}
