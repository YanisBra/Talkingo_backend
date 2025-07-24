<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720193037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_result (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, theme_id INT NOT NULL, language_id INT NOT NULL, score INT NOT NULL, question_count INT NOT NULL, end_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_FE2E314AA76ED395 (user_id), INDEX IDX_FE2E314A59027487 (theme_id), INDEX IDX_FE2E314A82F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314A59027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314A82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C5BA14FCCC ON `group` (invitation_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314AA76ED395');
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314A59027487');
        $this->addSql('ALTER TABLE quiz_result DROP FOREIGN KEY FK_FE2E314A82F1BAF4');
        $this->addSql('DROP TABLE quiz_result');
        $this->addSql('DROP INDEX UNIQ_6DC044C5BA14FCCC ON `group`');
    }
}
