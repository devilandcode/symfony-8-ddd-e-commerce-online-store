<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\FindCategory;

use App\Catalog\Application\Category\AdminUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;

final readonly class FindCategoryHandler
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {}

    public function __invoke(int $id): CategoryViewDTO
    {
        $category = $this->categoryRepository->findById($id) ?? throw CategoryNotFoundException::withId($id);

        return CategoryViewDTO::fromCategory($category);
    }
}
