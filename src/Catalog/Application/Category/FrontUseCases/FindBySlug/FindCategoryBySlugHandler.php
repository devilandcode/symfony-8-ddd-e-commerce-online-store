<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\FrontUseCases\FindBySlug;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\Output\ViewCategoryDTO;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;

final readonly class FindCategoryBySlugHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ){}

    public function __invoke(Slug $slug): ?ViewCategoryDTO
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if ($category === null) {
            throw CategoryNotFoundException::withSlug($slug->toString());
        }

        return new ViewCategoryDTO(
            title: $category->getTitle(),
            h1: $category->getH1(),
            desc: $category->getDesc(),
            content: $category->getContent(),
            seoTitle: $category->getSeoTitle(),
            seoDescription: $category->getSeoDescription(),
        );
    }
}
