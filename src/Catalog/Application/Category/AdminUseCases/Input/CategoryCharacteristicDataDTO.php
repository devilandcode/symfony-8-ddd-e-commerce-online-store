<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Input;

final readonly class CategoryCharacteristicDataDTO
{
    public function __construct(
        public int $characteristicId,
        public bool $isActive,
        public bool $inFilter,
    ) {
    }
}
