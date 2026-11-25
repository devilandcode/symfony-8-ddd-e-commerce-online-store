<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Handler;

use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\Shared\Domain\ValueObject\Slug;
use App\Storefront\Application\CatalogPage\CatalogPageComposer;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use App\Storefront\Application\CatalogPage\Output\ViewCatalogPageDTO;

final readonly class GetCategoryCatalogPageHandler
{
    public function __construct(
        private FindCategoryBySlugHandler $findCategoryBySlug,
        private FindChildCategoryHandler $findChildCategory,
        private CatalogPageComposer $composer,
    ) {
    }

    public function __invoke(
        ?string $parentSlug,
        string $slug,
        CatalogPageFilters $filters,
        PageNumber $pageNumber,
    ): ViewCatalogPageDTO {
        $category = new Slug($slug);
        $categoryView = null === $parentSlug
            ? ($this->findCategoryBySlug)($category)
            : ($this->findChildCategory)(new Slug($parentSlug), $category);

        return $this->composer->compose($categoryView, $category->toString(), $filters, $pageNumber);
    }
}
