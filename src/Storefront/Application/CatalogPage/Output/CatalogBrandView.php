<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Output;

final readonly class CatalogBrandView
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
