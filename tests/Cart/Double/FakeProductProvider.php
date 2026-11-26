<?php

declare(strict_types=1);

namespace App\Tests\Cart\Double;

use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;

final readonly class FakeProductProvider implements PurchasableProductProviderInterface
{
    /** @param array<int, PurchasableProductDTO> $products */
    public function __construct(private array $products)
    {
    }

    public function find(int $productId): ?PurchasableProductDTO
    {
        return $this->products[$productId] ?? null;
    }
}
