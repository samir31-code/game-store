<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260319163924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, numero_commande VARCHAR(20) NOT NULL, cout_total_ht NUMERIC(10, 2) NOT NULL, cout_total_ttc NUMERIC(10, 2) NOT NULL, type_paiement VARCHAR(30) NOT NULL, paiement_valide TINYINT NOT NULL, date_commande DATETIME NOT NULL, panier_sauvegarde TINYINT NOT NULL, adresse_livraison_id INT DEFAULT NULL, INDEX IDX_6EEAA67DBE2F0A35 (adresse_livraison_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DBE2F0A35 FOREIGN KEY (adresse_livraison_id) REFERENCES adresse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DBE2F0A35');
        $this->addSql('DROP TABLE commande');
    }
}
