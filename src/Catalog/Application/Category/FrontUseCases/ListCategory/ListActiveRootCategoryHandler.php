<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\FrontUseCases\ListCategory;

use App\Catalog\Domain\Repository\CategoryRepositoryInterface;

final readonly class ListActiveRootCategoryHandler
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {}

    /**
     * @return list<Category>
     */
    public function __invoke(): array
    {
        return $this->categoryRepository->getRootCategories();
    }
}
