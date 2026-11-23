<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Output;

final readonly class ProductIndexView
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public int $priceMinorUnits,
        public string $currencyCode,
        public int $balance,
        public string $categoryTitle,
        public string $brandTitle,
        public bool $isNew,
        public bool $isHit,
        public bool $isActive,
    ) {
    }
}
