<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

final readonly class CartDTO
{
    /**
     * @param list<CartItemDTO>  $items
     * @param list<CartTotalDTO> $totals one entry per currency present in the cart
     */
    public function __construct(
        public array $items,
        public array $totals,
    ) {
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }
}
