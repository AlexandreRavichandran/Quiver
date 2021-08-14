<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210814092307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answers_liked (answer_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_BAC54E69AA334807 (answer_id), INDEX IDX_BAC54E69A76ED395 (user_id), PRIMARY KEY(answer_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE answers_disliked (answer_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E87E423EAA334807 (answer_id), INDEX IDX_E87E423EA76ED395 (user_id), PRIMARY KEY(answer_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answers_liked ADD CONSTRAINT FK_BAC54E69AA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answers_liked ADD CONSTRAINT FK_BAC54E69A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answers_disliked ADD CONSTRAINT FK_E87E423EAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answers_disliked ADD CONSTRAINT FK_E87E423EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE answers_liked');
        $this->addSql('DROP TABLE answers_disliked');
    }
}
