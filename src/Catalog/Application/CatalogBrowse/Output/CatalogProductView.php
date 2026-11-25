<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse\Output;

final readonly class CatalogProductView
{
    public function __construct(
        public string $title,
        public string $brandTitle,
        public ?string $image,
        public string $price,
        public ?string $oldPrice,
        public bool $isNew,
        public bool $isHit,
    ) {
    }
}
