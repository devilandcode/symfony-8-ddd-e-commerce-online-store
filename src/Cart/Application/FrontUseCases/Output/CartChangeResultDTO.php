<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

/** The state of the cart right after a successful change, enough to refresh a cart page and header counter. */
final readonly class CartChangeResultDTO
{
    public function __construct(
        public CartSummaryDTO $summary,
        public CartDTO $cart,
    ) {
    }
}
