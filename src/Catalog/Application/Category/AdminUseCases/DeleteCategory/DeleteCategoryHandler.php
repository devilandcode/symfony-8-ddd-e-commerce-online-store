<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\DeleteCategory;

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

        if (!$category) {
            throw new \InvalidArgumentException(sprintf('Category with ID %d not found.', $id));
        }

        $this->categoryRepository->delete($category);
        $this->flusher->commit();
    }
}
