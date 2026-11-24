<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261124113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add an optional image path to brands.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE brand ADD image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE brand DROP image');
    }
}
