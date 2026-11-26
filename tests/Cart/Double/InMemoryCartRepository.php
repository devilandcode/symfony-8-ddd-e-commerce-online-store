<?php

declare(strict_types=1);

namespace App\Tests\Cart\Double;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Cart\Domain\ValueObject\CartOwner;

final class InMemoryCartRepository implements CartRepositoryInterface
{
    /** @var list<Cart> */
    private array $carts = [];

    public function findByOwner(CartOwner $owner): ?Cart
    {
        foreach ($this->carts as $cart) {
            if ($cart->getOwner()->equals($owner)) {
                return $cart;
            }
        }

        return null;
    }

    public function save(Cart $cart): void
    {
        if (!\in_array($cart, $this->carts, true)) {
            $this->carts[] = $cart;
        }
    }

    /** @return list<Cart> */
    public function all(): array
    {
        return $this->carts;
    }
}
