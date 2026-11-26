<?php

declare(strict_types=1);

namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\ValueObject\CartOwner;

interface CartRepositoryInterface
{
    public function findByOwner(CartOwner $owner): ?Cart;

    public function save(Cart $cart): void;
}
