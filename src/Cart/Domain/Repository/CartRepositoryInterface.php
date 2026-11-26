<?php

declare(strict_types=1);

namespace App\Cart\Domain\Repository;

use App\Cart\Domain\Entity\Cart;

interface CartRepositoryInterface
{
    public function findAll(): array;

    public function findByHash(string $hash): array;

    public function findByUserId(int $userId): array;
    
    public function save(Cart $cart): void;

    public function remove(Cart $cart): void;
}
