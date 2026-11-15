<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\ListCategory;

use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;

final readonly class ListCategoryHandler
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {}

    /**
     * @return list<CategoryViewDTO>
     */
    public function __invoke(): array
    {
        return array_map(CategoryViewDTO::fromCategory(...), $this->categoryRepository->findAll());
    }
}
