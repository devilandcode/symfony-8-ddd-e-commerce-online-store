<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse\Output;

final readonly class CatalogBrandView
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
