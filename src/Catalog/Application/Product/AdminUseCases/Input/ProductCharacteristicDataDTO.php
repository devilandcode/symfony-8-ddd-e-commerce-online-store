<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Input;

final readonly class ProductCharacteristicDataDTO
{
    public function __construct(
        public int $characteristicId,
        public string $value,
    ) {
    }
}
