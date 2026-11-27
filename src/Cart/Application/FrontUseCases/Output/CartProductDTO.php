<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

/** Catalog data the cart page needs about one product. Money is expressed in integer minor units. */
final readonly class CartProductDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $brandTitle,
        public ?string $image,
        public int $priceMinorUnits,
        public ?int $oldPriceMinorUnits,
        public string $currencyCode,
        public int $availableUnits,
    ) {
    }
}
