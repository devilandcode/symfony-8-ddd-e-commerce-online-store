<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage;

use App\Catalog\Application\Category\FrontUseCases\Output\ViewCategoryDTO;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use App\Storefront\Application\CatalogPage\Interface\CatalogPageQueryInterface;
use App\Storefront\Application\CatalogPage\Output\ViewCatalogPageDTO;

/**
 * Assembles the parts shared by the root and category catalog pages:
 * sidebar data, the product page and the configured page size.
 */
final readonly class CatalogPageComposer
{
    public function __construct(
        private CatalogPageQueryInterface $catalogQuery,
        private ListContentSettingHandler $listContentSetting,
    ) {
    }

    public function compose(
        ViewPageDTO|ViewCategoryDTO $page,
        ?string $categorySlug,
        CatalogPageFilters $filters,
        PageNumber $pageNumber,
    ): ViewCatalogPageDTO {
        // The root page filters by category ids; a category page is already scoped by its slug
        // and offers characteristic filters instead.
        $productFilters = null === $categorySlug
            ? new CatalogPageFilters(categoryIds: $filters->categoryIds, brandIds: $filters->brandIds)
            : new CatalogPageFilters(brandIds: $filters->brandIds, characteristicValues: $filters->characteristicValues);

        return new ViewCatalogPageDTO(
            page: $page,
            categorySlug: $categorySlug,
            categories: $this->catalogQuery->findActiveCategories(),
            brands: $this->catalogQuery->findActiveBrands($categorySlug),
            characteristics: null === $categorySlug
                ? []
                : $this->catalogQuery->findFilterableCharacteristics($categorySlug),
            productPage: $this->catalogQuery->findProducts(
                $categorySlug,
                $productFilters,
                $pageNumber,
                ($this->listContentSetting)()->productsLimit(),
            ),
            filters: $filters,
        );
    }
}
