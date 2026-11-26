<?php

declare(strict_types=1);

namespace App\Cart\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Internal entity of the Cart aggregate. It can only be changed through the Cart aggregate root.
 */
#[ORM\Entity]
#[ORM\Table(name: 'cart_item')]
#[ORM\UniqueConstraint(name: 'uniq_cart_item_cart_product', columns: ['cart_id', 'product_id'])]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', foreignKeyName: 'fk_cart_item_cart')]
    private Cart $cart;

    #[ORM\Column(name: 'product_id', type: 'bigint', nullable: false)]
    private int $productId;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    /** @internal Create items through Cart::addProduct() only. */
    public function __construct(Cart $cart, int $productId, int $quantity)
    {
        $this->cart = $cart;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /** @internal Called by the Cart aggregate root after it validated the new quantity. */
    public function changeQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
