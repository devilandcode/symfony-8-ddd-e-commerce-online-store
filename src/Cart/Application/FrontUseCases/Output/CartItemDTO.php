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
        public int $availableUnits,
        public ?string $volume = null,
        public ?string $color = null,
    ) {
    }

    /** True while the cart quantity is below the stock balance, i.e. one more unit can be added. */
    public function canIncreaseQuantity(): bool
    {
        return $this->quantity < $this->availableUnits;
    }

    /** Whole-percent discount of the old price versus the current price, when an old price exists. */
    public function discountPercent(): ?int
    {
        if (null === $this->unitOldPriceMinorUnits || $this->unitOldPriceMinorUnits <= $this->unitPriceMinorUnits) {
            return null;
        }

        return intdiv(($this->unitOldPriceMinorUnits - $this->unitPriceMinorUnits) * 100, $this->unitOldPriceMinorUnits);
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
