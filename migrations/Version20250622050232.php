<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622050232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA66458F28E
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bank_cards (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, number VARCHAR(255) NOT NULL, expiration_date DATETIME NOT NULL, cvv VARCHAR(3) NOT NULL, type VARCHAR(50) NOT NULL, INDEX IDX_E689688A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bank_cards ADD CONSTRAINT FK_E689688A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bank_card DROP FOREIGN KEY FK_BC74CA5DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bank_card
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2D3A8DA66458F28E ON expense
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD bank_cards_id INT DEFAULT NULL, DROP bank_card_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6ED154BF0 FOREIGN KEY (bank_cards_id) REFERENCES bank_cards (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2D3A8DA6ED154BF0 ON expense (bank_cards_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6ED154BF0
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bank_card (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, number VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expiration_date DATETIME NOT NULL, cvv VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_BC74CA5DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bank_card ADD CONSTRAINT FK_BC74CA5DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bank_cards DROP FOREIGN KEY FK_E689688A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bank_cards
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2D3A8DA6ED154BF0 ON expense
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD bank_card_id INT NOT NULL, DROP bank_cards_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA66458F28E FOREIGN KEY (bank_card_id) REFERENCES bank_card (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2D3A8DA66458F28E ON expense (bank_card_id)
        SQL);
    }
}
