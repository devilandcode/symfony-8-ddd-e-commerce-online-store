<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\FindProduct;

use App\Catalog\Application\Product\AdminUseCases\Exception\ProductNotFoundException;
use App\Catalog\Application\Product\AdminUseCases\Output\ProductViewDTO;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;

final readonly class FindProductHandler
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
    }

    public function __invoke(int $id): ProductViewDTO
    {
        $product = $this->productRepository->findById($id) ?? throw ProductNotFoundException::withId($id);

        return ProductViewDTO::fromProduct($product);
    }
}
