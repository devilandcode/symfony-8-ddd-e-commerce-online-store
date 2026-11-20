<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\ListProducts;

use App\Catalog\Application\Product\AdminUseCases\Output\ProductIndexView;

final readonly class ListProductsHandler
{
    public function __construct(private ProductIndexQueryInterface $productIndexQuery)
    {
    }

    /** @return list<ProductIndexView> */
    public function __invoke(): array
    {
        return $this->productIndexQuery->findAll();
    }
}
