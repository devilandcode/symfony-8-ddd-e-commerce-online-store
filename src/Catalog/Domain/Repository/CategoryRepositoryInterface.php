<?php

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Shared\Domain\ValueObject\Slug;

interface CategoryRepositoryInterface
{
    public function findAll(): array;
    
    /** @return list<Category> */
    public function getRootCategories(): array;

    public function findById(int $id): ?Category;

    public function findBySlug(Slug $slug): ?Category;

    public function hasProducts(Category $category): bool;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}
