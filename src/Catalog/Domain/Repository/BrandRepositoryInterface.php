<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Brand;
use App\Shared\Domain\ValueObject\Slug;

interface BrandRepositoryInterface
{
    /** @return list<Brand> */
    public function findAll(): array;

    public function findById(int $id): ?Brand;

    public function findBySlug(Slug $slug): ?Brand;

    public function hasProducts(Brand $brand): bool;

    public function save(Brand $brand): void;

    public function delete(Brand $brand): void;
}
