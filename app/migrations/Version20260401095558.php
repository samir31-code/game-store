<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401095558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_assistance (id INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, date_reponse DATETIME DEFAULT NULL, reponse LONGTEXT DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_ED838F5319EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(150) NOT NULL, contenu LONGTEXT NOT NULL, date_envoi DATETIME NOT NULL, lu TINYINT NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT NOT NULL, INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX IDX_B6BD307FA4F84F6E (destinataire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE demande_assistance ADD CONSTRAINT FK_ED838F5319EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_assistance DROP FOREIGN KEY FK_ED838F5319EB6921');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4F84F6E');
        $this->addSql('DROP TABLE demande_assistance');
        $this->addSql('DROP TABLE message');
    }
}
