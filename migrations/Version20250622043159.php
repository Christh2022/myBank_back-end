<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622043159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD bank_card_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA66458F28E FOREIGN KEY (bank_card_id) REFERENCES bank_card (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2D3A8DA66458F28E ON expense (bank_card_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA66458F28E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2D3A8DA66458F28E ON expense
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense DROP bank_card_id
        SQL);
    }
}
