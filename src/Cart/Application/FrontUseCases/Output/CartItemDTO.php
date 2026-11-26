<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

final readonly class CartItemDTO
{
    public function __construct(
        public int $productId,
        public string $title,
        public string $brandTitle,
        public ?string $image,
        public int $quantity,
        public int $unitPriceMinorUnits,
        public ?int $unitOldPriceMinorUnits,
        public string $currencyCode,
    ) {
    }

    public function totalMinorUnits(): int
    {
        return $this->unitPriceMinorUnits * $this->quantity;
    }

    public function oldTotalMinorUnits(): ?int
    {
        return null === $this->unitOldPriceMinorUnits ? null : $this->unitOldPriceMinorUnits * $this->quantity;
    }
}
