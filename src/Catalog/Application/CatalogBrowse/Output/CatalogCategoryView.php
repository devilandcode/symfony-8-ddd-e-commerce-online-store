<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse\Output;

final readonly class CatalogCategoryView
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $parentSlug,
    ) {
    }
}
