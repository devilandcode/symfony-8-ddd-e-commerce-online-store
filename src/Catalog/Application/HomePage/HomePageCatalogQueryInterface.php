<?php

declare(strict_types=1);

namespace App\Catalog\Application\HomePage;

interface HomePageCatalogQueryInterface
{
    /** @return list<HomePageProductView> */
    public function findNewProducts(int $limit): array;

    /** @return list<HomePageProductView> */
    public function findHitProducts(int $limit): array;

    /** @return list<HomePageBrandView> */
    public function findPopularBrands(int $limit): array;
}
