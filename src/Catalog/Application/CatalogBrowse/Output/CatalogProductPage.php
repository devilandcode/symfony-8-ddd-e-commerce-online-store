<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse\Output;

final readonly class CatalogProductPage
{
    /**
     * @param list<CatalogProductView> $products
     */
    public function __construct(
        public array $products,
        public int $totalProducts,
        public int $currentPage,
        public int $pageSize,
        public int $totalPages,
    ) {
    }
}
