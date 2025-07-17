<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717093400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_phrase_progress (id INT AUTO_INCREMENT NOT NULL, phrase_translation_id INT NOT NULL, user_id INT NOT NULL, is_known TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AF4E953D3CC3F0BC (phrase_translation_id), INDEX IDX_AF4E953DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_phrase_progress ADD CONSTRAINT FK_AF4E953D3CC3F0BC FOREIGN KEY (phrase_translation_id) REFERENCES phrase_translation (id)');
        $this->addSql('ALTER TABLE user_phrase_progress ADD CONSTRAINT FK_AF4E953DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL, DROP role');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_phrase_progress DROP FOREIGN KEY FK_AF4E953D3CC3F0BC');
        $this->addSql('ALTER TABLE user_phrase_progress DROP FOREIGN KEY FK_AF4E953DA76ED395');
        $this->addSql('DROP TABLE user_phrase_progress');
        $this->addSql('ALTER TABLE user ADD role VARCHAR(50) NOT NULL, DROP roles');
    }
}
