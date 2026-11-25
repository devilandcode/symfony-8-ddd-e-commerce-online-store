<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse;

use App\Catalog\Application\CatalogBrowse\Input\CatalogBrowseCriteria;
use App\Catalog\Application\CatalogBrowse\Output\CatalogBrandView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogCategoryView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogCharacteristicFilterView;
use App\Catalog\Application\CatalogBrowse\Output\CatalogProductPage;

interface CatalogBrowseQueryInterface
{
    /** @return list<CatalogCategoryView> */
    public function findActiveCategories(): array;

    /** @return list<CatalogBrandView> */
    public function findActiveBrands(?string $categorySlug): array;

    /** @return list<CatalogCharacteristicFilterView> */
    public function findFilterableCharacteristics(string $categorySlug): array;

    public function findProducts(
        CatalogBrowseCriteria $criteria,
        int $page,
        int $pageSize,
    ): CatalogProductPage;
}
