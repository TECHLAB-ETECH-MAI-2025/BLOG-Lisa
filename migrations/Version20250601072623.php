<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601072623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP CONSTRAINT fk_b6bd307f1c779ca5
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_b6bd307f1c779ca5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ALTER content DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ALTER created_at DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME COLUMN receveir_id TO receiver_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B6BD307FCD53EDB6 ON message (receiver_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP CONSTRAINT FK_B6BD307FCD53EDB6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B6BD307FCD53EDB6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ALTER content SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ALTER created_at SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME COLUMN receiver_id TO receveir_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT fk_b6bd307f1c779ca5 FOREIGN KEY (receveir_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_b6bd307f1c779ca5 ON message (receveir_id)
        SQL);
    }
}
