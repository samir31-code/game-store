<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320095616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero_facture VARCHAR(20) NOT NULL, date_facturation DATETIME NOT NULL, commande_id INT NOT NULL, adresse_facturation_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_FE86641082EA2E54 (commande_id), INDEX IDX_FE8664105BBD1224 (adresse_facturation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664105BBD1224 FOREIGN KEY (adresse_facturation_id) REFERENCES adresse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641082EA2E54');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664105BBD1224');
        $this->addSql('DROP TABLE facture');
    }
}
