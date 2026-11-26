<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Input;

final readonly class AddToCartDTO
{
    public function __construct(
        public int $productId,
        public int $quantity = 1,
    ) {
    }
}
