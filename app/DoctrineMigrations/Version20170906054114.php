<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170906054114 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE state TO states');
        $this->addSql('RENAME TABLE role_module TO role_modules');
        $this->addSql('RENAME TABLE  user TO users');
        $this->addSql('RENAME TABLE  country TO countries');
        $this->addSql('RENAME TABLE  profile TO profiles');
        $this->addSql('RENAME TABLE  role TO roles');
        $this->addSql('RENAME TABLE  module TO modules');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE state TO states');
        $this->addSql('RENAME TABLE role_module TO role_modules');
        $this->addSql('RENAME TABLE  user TO users');
        $this->addSql('RENAME TABLE  country TO countries');
        $this->addSql('RENAME TABLE  profile TO profiles');
        $this->addSql('RENAME TABLE  role TO roles');
        $this->addSql('RENAME TABLE  module TO modules');

    }
}
