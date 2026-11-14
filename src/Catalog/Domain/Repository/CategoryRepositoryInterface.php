<?php

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Shared\Domain\ValueObject\Slug;

interface CategoryRepositoryInterface
{
    /** @return list<Category> */
    public function getRootCategories(): array;

    public function findBySlug(Slug $slug): ?Category;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}
