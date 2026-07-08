<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630070703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD civilite VARCHAR(20) DEFAULT NULL, ADD date_naissance DATE DEFAULT NULL, ADD type_compte VARCHAR(20) DEFAULT NULL, ADD accepte_offres_email TINYINT DEFAULT NULL, ADD accepte_offres_sms TINYINT DEFAULT NULL, ADD accepte_offres_partenaires TINYINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP civilite, DROP date_naissance, DROP type_compte, DROP accepte_offres_email, DROP accepte_offres_sms, DROP accepte_offres_partenaires');
    }
}
