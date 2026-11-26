<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

/** Totals for one currency. Currencies are never mixed or converted. */
final readonly class CartTotalDTO
{
    public function __construct(
        public string $currencyCode,
        public int $productsMinorUnits,
        public int $discountMinorUnits,
        public int $totalMinorUnits,
    ) {
    }
}
