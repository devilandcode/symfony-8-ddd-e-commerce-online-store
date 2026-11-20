<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\ListProducts;

use App\Catalog\Application\Product\AdminUseCases\Output\ProductIndexView;

interface ProductIndexQueryInterface
{
    /** @return list<ProductIndexView> */
    public function findAll(): array;
}
