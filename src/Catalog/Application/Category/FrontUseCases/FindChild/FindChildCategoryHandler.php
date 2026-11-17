<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\FrontUseCases\FindChild;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\Output\ViewCategoryDTO;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;

final readonly class FindChildCategoryHandler
{
    public function __construct(private CategoryRepositoryInterface $categoryRepository)
    {}

    public function __invoke(Slug $parentSlug, Slug $slug): ViewCategoryDTO
    {
        $category = $this->categoryRepository->findBySlug($slug);
        $parent = $category?->getParent();

        if (
            null === $category
            || !$category->getIsActive()
            || null === $parent
            || !$parent->getIsActive()
            || $parent->getSlug()->toString() !== $parentSlug->toString()
        ) {
            throw CategoryNotFoundException::withSlug($slug->toString());
        }

        return ViewCategoryDTO::fromCategory($category);
    }
}
