<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218112217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD admin_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66DF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_23A0E66DF6E65AD ON article (admin_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66DF6E65AD');
        $this->addSql('DROP INDEX IDX_23A0E66DF6E65AD ON article');
        $this->addSql('ALTER TABLE article DROP admin_id_id');
    }
}
