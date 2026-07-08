<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319160638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, reference_produit VARCHAR(50) NOT NULL, nom VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, marque VARCHAR(100) DEFAULT NULL, dimension_l NUMERIC(8, 2) DEFAULT NULL, dimension_h NUMERIC(8, 2) DEFAULT NULL, dimension_p NUMERIC(8, 2) DEFAULT NULL, poids NUMERIC(8, 3) DEFAULT NULL, etat_stock VARCHAR(20) NOT NULL, quantite_stock INT NOT NULL, prix_ht NUMERIC(10, 2) NOT NULL, tva_produit NUMERIC(5, 2) NOT NULL, prix_ttc NUMERIC(10, 2) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE produit');
    }
}
