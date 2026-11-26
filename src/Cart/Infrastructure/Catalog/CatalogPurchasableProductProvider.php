<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;

final readonly class CatalogPurchasableProductProvider implements PurchasableProductProviderInterface
{
    public function __construct(private ProductRepositoryInterface $products)
    {
    }

    public function find(int $productId): ?PurchasableProductDTO
    {
        $product = $this->products->findById($productId);

        return null === $product
            ? null
            : new PurchasableProductDTO($productId, $product->isActive(), $product->getBalance()->getUnits());
    }
}
