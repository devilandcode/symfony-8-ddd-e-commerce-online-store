<?php

declare(strict_types=1);

namespace App\Cart\Application\FrontUseCases\Output;

final readonly class PurchasableProductDTO
{
    public function __construct(
        public int $id,
        public bool $isActive,
        public int $availableUnits,
    ) {
    }

    public function canBePurchased(): bool
    {
        return $this->isActive && $this->availableUnits > 0;
    }
}
