<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320093146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detail_commande (id INT AUTO_INCREMENT NOT NULL, cout_unitaire_ht NUMERIC(10, 2) NOT NULL, cout_total_ht NUMERIC(10, 2) NOT NULL, tva_produit NUMERIC(5, 2) NOT NULL, quantite INT NOT NULL, cout_unitaire_ttc NUMERIC(10, 2) NOT NULL, cout_total_ttc NUMERIC(10, 2) NOT NULL, commande_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_98344FA682EA2E54 (commande_id), INDEX IDX_98344FA6F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA682EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE detail_commande ADD CONSTRAINT FK_98344FA6F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA682EA2E54');
        $this->addSql('ALTER TABLE detail_commande DROP FOREIGN KEY FK_98344FA6F347EFB');
        $this->addSql('DROP TABLE detail_commande');
    }
}
