<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Product;
use App\Shared\Domain\ValueObject\Slug;

interface ProductRepositoryInterface
{
    /** @return list<Product> */
    public function findAll(): array;

    public function findById(int $id): ?Product;

    public function findBySlug(Slug $slug): ?Product;

    public function save(Product $product): void;

    public function delete(Product $product): void;
}
