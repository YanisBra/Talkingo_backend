<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250719164007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, target_language_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, invitation_code VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6DC044C55CBF5FE (target_language_id), INDEX IDX_6DC044C5B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_membership (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, target_group_id INT DEFAULT NULL, joined_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5132B337A76ED395 (user_id), INDEX IDX_5132B33724FF092E (target_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C55CBF5FE FOREIGN KEY (target_language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_membership ADD CONSTRAINT FK_5132B337A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_membership ADD CONSTRAINT FK_5132B33724FF092E FOREIGN KEY (target_group_id) REFERENCES `group` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C55CBF5FE');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5B03A8386');
        $this->addSql('ALTER TABLE group_membership DROP FOREIGN KEY FK_5132B337A76ED395');
        $this->addSql('ALTER TABLE group_membership DROP FOREIGN KEY FK_5132B33724FF092E');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_membership');
    }
}
