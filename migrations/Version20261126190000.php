<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the cart_item.product_id foreign key (ON DELETE CASCADE) and its index for the read-only CartItem -> Product association.';
    }

    public function up(Schema $schema): void
    {
        // A line pointing to a product that no longer exists is already invisible on the cart page; remove it so the FK can be created.
        $this->addSql('DELETE FROM cart_item WHERE product_id NOT IN (SELECT id FROM product)');
        $this->addSql('CREATE INDEX IDX_F0FE25274584665A ON cart_item (product_id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT fk_cart_item_product FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT fk_cart_item_product');
        $this->addSql('DROP INDEX IDX_F0FE25274584665A');
    }
}
