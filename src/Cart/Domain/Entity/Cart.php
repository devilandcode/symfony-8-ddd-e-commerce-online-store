<?php

declare(strict_types=1);

namespace App\Cart\Domain\Entity;

use App\Cart\Domain\Exception\ProductNotInCartException;
use App\Cart\Domain\ValueObject\CartOwner;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cart aggregate root. Owned either by an authenticated user (user_id) or by a guest (token).
 * Products are referenced by identifier only; Catalog internals never enter this aggregate.
 *
 * Mapped with Doctrine attributes, consistent with the rest of the project (documented architectural compromise).
 */
#[ORM\Entity]
#[ORM\Table(name: 'cart')]
#[ORM\UniqueConstraint(name: 'uniq_cart_user', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'uniq_cart_guest_token', columns: ['guest_token'])]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'bigint', unique: true)]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', type: 'bigint', nullable: true)]
    private ?int $userId;

    #[ORM\Column(name: 'guest_token', type: 'string', length: 64, nullable: true)]
    private ?string $guestToken;

    /** @var Collection<int, CartItem> */
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false)]
    private \DateTime $updatedAt;

    private function __construct(CartOwner $owner, \DateTime $now)
    {
        $this->userId = $owner->getUserId();
        $this->guestToken = $owner->getGuestToken();
        $this->items = new ArrayCollection();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function create(CartOwner $owner, \DateTime $now): self
    {
        return new self($owner, $now);
    }

    /** Adds a product, or increases its quantity when it is already in the cart. The line enforces the stock rule. */
    public function addProduct(int $productId, Quantity $quantity, StockLimit $stock, \DateTime $now): void
    {
        $item = $this->findItem($productId);

        if (null === $item) {
            $this->items->add(new CartItem($this, $productId, $quantity, $stock));
        } else {
            $item->increaseBy($quantity, $stock);
        }

        $this->updatedAt = $now;
    }

    /** Sets the exact quantity of a product that is already in the cart. Use removeProduct() to drop a line. */
    public function changeQuantity(int $productId, Quantity $quantity, StockLimit $stock, \DateTime $now): void
    {
        $item = $this->findItem($productId) ?? throw ProductNotInCartException::withProductId($productId);

        $item->changeTo($quantity, $stock);
        $this->updatedAt = $now;
    }

    public function removeProduct(int $productId, \DateTime $now): void
    {
        $item = $this->findItem($productId) ?? throw ProductNotInCartException::withProductId($productId);

        $this->items->removeElement($item);
        $this->updatedAt = $now;
    }

    public function containsProduct(int $productId): bool
    {
        return null !== $this->findItem($productId);
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /** Total number of units in the cart: the sum of all line quantities (not the number of lines). */
    public function getTotalQuantity(): int
    {
        return array_sum(array_map(
            static fn (CartItem $item): int => $item->getQuantity()->getValue(),
            $this->items->toArray(),
        ));
    }

    /** @return list<int> */
    public function getProductIds(): array
    {
        return array_values(array_map(
            static fn (CartItem $item): int => $item->getProductId(),
            $this->items->toArray(),
        ));
    }

    /** @return list<CartItem> */
    public function getItems(): array
    {
        return array_values($this->items->toArray());
    }

    public function getOwner(): CartOwner
    {
        return null !== $this->guestToken
            ? CartOwner::guest($this->guestToken)
            : CartOwner::user($this->userId ?? throw new \LogicException('Cart has no owner.'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    private function findItem(int $productId): ?CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getProductId() === $productId) {
                return $item;
            }
        }

        return null;
    }
}
