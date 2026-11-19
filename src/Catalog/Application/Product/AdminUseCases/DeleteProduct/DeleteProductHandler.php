<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\DeleteProduct;

use App\Catalog\Application\Product\AdminUseCases\Exception\ProductNotFoundException;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class DeleteProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $product = $this->productRepository->findById($id) ?? throw ProductNotFoundException::withId($id);
        $this->productRepository->delete($product);
        $this->flusher->commit();
    }
}
