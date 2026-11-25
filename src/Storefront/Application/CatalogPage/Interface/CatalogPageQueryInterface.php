<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Interface;

use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Output\CatalogBrandView;
use App\Storefront\Application\CatalogPage\Output\CatalogCategoryView;
use App\Storefront\Application\CatalogPage\Output\CatalogCharacteristicFilterView;
use App\Storefront\Application\CatalogPage\Output\CatalogProductPage;

interface CatalogPageQueryInterface
{
    /** @return list<CatalogCategoryView> */
    public function findActiveCategories(): array;

    /** @return list<CatalogBrandView> */
    public function findActiveBrands(?string $categorySlug): array;

    /** @return list<CatalogCharacteristicFilterView> */
    public function findFilterableCharacteristics(string $categorySlug): array;

    public function findProducts(
        ?string $categorySlug,
        CatalogPageFilters $filters,
        int $page,
        int $pageSize,
    ): CatalogProductPage;
}
