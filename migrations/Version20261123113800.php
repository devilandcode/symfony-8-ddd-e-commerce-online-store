<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261123113800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new and hit flags to products.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ADD is_new BOOLEAN DEFAULT FALSE NOT NULL, ADD is_hit BOOLEAN DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP is_new, DROP is_hit');
    }
}
