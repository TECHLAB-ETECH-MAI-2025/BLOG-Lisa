<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518174049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP CONSTRAINT fk_23a0e66f8697d13
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_23a0e66f8697d13
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP comment_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ALTER category_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP CONSTRAINT fk_64c19c17294869c
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_64c19c17294869c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category DROP article_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD article_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category ADD CONSTRAINT fk_64c19c17294869c FOREIGN KEY (article_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_64c19c17294869c ON category (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD comment_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ALTER category_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT fk_23a0e66f8697d13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_23a0e66f8697d13 ON article (comment_id)
        SQL);
    }
}
