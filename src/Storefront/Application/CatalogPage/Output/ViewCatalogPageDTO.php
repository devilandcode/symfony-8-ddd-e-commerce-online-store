<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Output;

use App\Catalog\Application\CatalogBrowse\Output\CatalogBrandView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogCategoryView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogCharacteristicFilterView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogProductPage;
use App\Catalog\Application\Category\FrontUseCases\Output\ViewCategoryDTO;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;

final readonly class ViewCatalogPageDTO
{
    /**
     * @param list<CatalogCategoryView> $categories
     * @param list<CatalogBrandView> $brands
     * @param list<CatalogCharacteristicFilterView> $characteristics
     */
    public function __construct(
        public ViewPageDTO|ViewCategoryDTO $page,
        public ?string $categorySlug,
        public array $categories,
        public array $brands,
        public array $characteristics,
        public CatalogProductPage $productPage,
        public CatalogPageFilters $filters,
    ) {
    }
}
