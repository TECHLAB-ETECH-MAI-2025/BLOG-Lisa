<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531053420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE article_category (article_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(article_id, category_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_53A4EDAA7294869C ON article_category (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_53A4EDAA12469DE2 ON article_category (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE article_like (id SERIAL NOT NULL, article_id INT DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1C21C7B27294869C ON article_like (article_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN article_like.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B27294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP CONSTRAINT fk_23a0e6612469de2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_23a0e6612469de2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP category_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category DROP CONSTRAINT FK_53A4EDAA7294869C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_category DROP CONSTRAINT FK_53A4EDAA12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_like DROP CONSTRAINT FK_1C21C7B27294869C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_like
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD category_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD CONSTRAINT fk_23a0e6612469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_23a0e6612469de2 ON article (category_id)
        SQL);
    }
}
