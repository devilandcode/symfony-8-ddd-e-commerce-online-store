<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

final readonly class CartSummaryDTO
{
    /** @param list<int> $productIds */
    public function __construct(
        public int $itemsCount,
        public array $productIds,
    ) {
    }

    public static function empty(): self
    {
        return new self(0, []);
    }

    public function contains(int $productId): bool
    {
        return \in_array($productId, $this->productIds, true);
    }
}
