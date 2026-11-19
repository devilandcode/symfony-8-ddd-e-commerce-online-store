<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\ListProducts;

use App\Catalog\Application\Product\AdminUseCases\Output\ProductViewDTO;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;

final readonly class ListProductsHandler
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    /** @return list<ProductViewDTO> */
    public function __invoke(): array
    {
        return array_map(ProductViewDTO::fromProduct(...), $this->productRepository->findAll());
    }
}
