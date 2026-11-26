<?php

declare(strict_types=1);

namespace App\Cart\Domain\Entity;

use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\CartOwner;
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
    public const int MAX_QUANTITY_PER_PRODUCT = 99;

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

    /**
     * Adds a product, or increases its quantity when it is already in the cart.
     *
     * @param int $availableUnits stock currently available for the product (supplied by the caller from Catalog)
     */
    public function addProduct(int $productId, int $quantity, int $availableUnits, \DateTime $now): void
    {
        if ($quantity < 1) {
            throw InvalidCartQuantityException::mustBePositive($quantity);
        }

        $item = $this->findItem($productId);
        $newQuantity = ($item?->getQuantity() ?? 0) + $quantity;
        $allowed = min(self::MAX_QUANTITY_PER_PRODUCT, $availableUnits);

        if ($newQuantity > $allowed) {
            throw InvalidCartQuantityException::limitExceeded($allowed);
        }

        if (null === $item) {
            $this->items->add(new CartItem($this, $productId, $newQuantity));
        } else {
            $item->changeQuantity($newQuantity);
        }

        $this->updatedAt = $now;
    }

    public function containsProduct(int $productId): bool
    {
        return null !== $this->findItem($productId);
    }

    /** Number of distinct products in the cart. */
    public function itemsCount(): int
    {
        return $this->items->count();
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
