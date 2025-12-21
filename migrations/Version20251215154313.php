<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215154313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates world events and calendar system tables with default calendar';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE calendar_months (id INT AUTO_INCREMENT NOT NULL, month_number INT NOT NULL, name VARCHAR(100) NOT NULL, days_count INT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE world_events (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_year INT NOT NULL, start_month INT NOT NULL, start_day INT NOT NULL, end_year INT DEFAULT NULL, end_month INT DEFAULT NULL, end_day INT DEFAULT NULL, color VARCHAR(7) DEFAULT \'#3498db\' NOT NULL, display_order INT NOT NULL, significance LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Insert default calendar (12 months, 30 days each = 360 day year)
        $this->addSql("INSERT INTO calendar_months (month_number, name, days_count, description, created_at) VALUES 
            (1, 'Primis', 30, 'Premier mois de l\'année', NOW()),
            (2, 'Secundus', 30, 'Deuxième mois', NOW()),
            (3, 'Tertius', 30, 'Troisième mois', NOW()),
            (4, 'Quartus', 30, 'Quatrième mois', NOW()),
            (5, 'Quintus', 30, 'Cinquième mois', NOW()),
            (6, 'Sextus', 30, 'Sixième mois', NOW()),
            (7, 'Septimus', 30, 'Septième mois', NOW()),
            (8, 'Octavus', 30, 'Huitième mois', NOW()),
            (9, 'Nonus', 30, 'Neuvième mois', NOW()),
            (10, 'Decimus', 30, 'Dixième mois', NOW()),
            (11, 'Undecimus', 30, 'Onzième mois', NOW()),
            (12, 'Duodecimus', 30, 'Douzième mois', NOW())
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE calendar_months');
        $this->addSql('DROP TABLE world_events');
    }
}
