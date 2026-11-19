<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\DeleteCategory;

use App\Catalog\Application\Category\AdminUseCases\Exception\CategoryHasProductsException;
use App\Catalog\Application\Category\AdminUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $category = $this->categoryRepository->findById($id);

        if (null === $category) {
            throw CategoryNotFoundException::withId($id);
        }

        if ($this->categoryRepository->hasProducts($category)) {
            throw CategoryHasProductsException::withId($id);
        }

        $this->categoryRepository->delete($category);
        $this->flusher->commit();
    }
}
