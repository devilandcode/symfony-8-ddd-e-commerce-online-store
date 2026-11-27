<?php

declare(strict_types=1);

namespace App\Cart\Domain\Entity;

use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use App\Catalog\Domain\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Internal entity of the Cart aggregate. It owns its quantity and the stock rule for it,
 * and can only be reached from outside through the Cart aggregate root.
 *
 * ARCHITECTURAL COMPROMISE (explicit product request): $product is a read-only Doctrine association to the
 * Catalog Product, mapped on the same product_id column as $productId. It exists only so read queries can join
 * Catalog data (stock balance, price) in one query. Rules:
 * - No domain behavior reads or depends on $product; Cart rules use $productId and a StockLimit passed in.
 * - The field $productId is the write model; it is mapped non-insertable/non-updatable because Doctrine writes the
 *   column through the association, which App\Cart\Infrastructure\Persistence\Doctrine\CartItemProductLinker fills.
 * - The association has no getter; do not add one. Prefer the Cart read port (CartProductProviderInterface).
 * The cleaner design is to keep referencing the product by id only and read Catalog through the port.
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

    #[ORM\Column(name: 'product_id', type: 'bigint', nullable: false, insertable: false, updatable: false)]
    private int $productId;

    /** Read-only side of the product_id column, see the class docblock. Never used by domain behavior. */
    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', foreignKeyName: 'fk_cart_item_product')]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quantity;

    /** @internal Create items through Cart::addProduct() only. */
    public function __construct(Cart $cart, int $productId, Quantity $quantity, StockLimit $stock)
    {
        $stock->ensureAllows($quantity);

        $this->cart = $cart;
        $this->productId = $productId;
        $this->quantity = $quantity->getValue();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): Quantity
    {
        return Quantity::of($this->quantity);
    }

    /** @internal Called by the Cart aggregate root. */
    public function increaseBy(Quantity $addition, StockLimit $stock): void
    {
        $this->changeTo($this->getQuantity()->add($addition), $stock);
    }

    /** @internal Called by the Cart aggregate root. */
    public function changeTo(Quantity $quantity, StockLimit $stock): void
    {
        $stock->ensureAllows($quantity);

        $this->quantity = $quantity->getValue();
    }
}
