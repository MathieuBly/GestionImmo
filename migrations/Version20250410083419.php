<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410083419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property ADD id_company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE32119A01 FOREIGN KEY (id_company_id) REFERENCES company (id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE32119A01 ON property (id_company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE32119A01');
        $this->addSql('DROP INDEX IDX_8BF21CDE32119A01 ON property');
        $this->addSql('ALTER TABLE property DROP id_company_id');
    }
}
