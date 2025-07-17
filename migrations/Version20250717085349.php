<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717085349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, label VARCHAR(100) NOT NULL, is_active TINYINT(1) NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D4DB71B577153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phrase (id INT AUTO_INCREMENT NOT NULL, theme_id INT NOT NULL, code VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A24BE60C59027487 (theme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phrase_translation (id INT AUTO_INCREMENT NOT NULL, phrase_id INT NOT NULL, language_id INT NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_8C5685C98671F084 (phrase_id), INDEX IDX_8C5685C982F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme_translation (id INT AUTO_INCREMENT NOT NULL, theme_id INT NOT NULL, language_id INT NOT NULL, label VARCHAR(100) NOT NULL, INDEX IDX_5C42566059027487 (theme_id), INDEX IDX_5C42566082F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, interface_language_id INT NOT NULL, target_language_id INT NOT NULL, email VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, role VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8D93D649590AB055 (interface_language_id), INDEX IDX_8D93D6495CBF5FE (target_language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE phrase ADD CONSTRAINT FK_A24BE60C59027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE phrase_translation ADD CONSTRAINT FK_8C5685C98671F084 FOREIGN KEY (phrase_id) REFERENCES phrase (id)');
        $this->addSql('ALTER TABLE phrase_translation ADD CONSTRAINT FK_8C5685C982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE theme_translation ADD CONSTRAINT FK_5C42566059027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE theme_translation ADD CONSTRAINT FK_5C42566082F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649590AB055 FOREIGN KEY (interface_language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495CBF5FE FOREIGN KEY (target_language_id) REFERENCES language (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE phrase DROP FOREIGN KEY FK_A24BE60C59027487');
        $this->addSql('ALTER TABLE phrase_translation DROP FOREIGN KEY FK_8C5685C98671F084');
        $this->addSql('ALTER TABLE phrase_translation DROP FOREIGN KEY FK_8C5685C982F1BAF4');
        $this->addSql('ALTER TABLE theme_translation DROP FOREIGN KEY FK_5C42566059027487');
        $this->addSql('ALTER TABLE theme_translation DROP FOREIGN KEY FK_5C42566082F1BAF4');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649590AB055');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495CBF5FE');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE phrase');
        $this->addSql('DROP TABLE phrase_translation');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE theme_translation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
