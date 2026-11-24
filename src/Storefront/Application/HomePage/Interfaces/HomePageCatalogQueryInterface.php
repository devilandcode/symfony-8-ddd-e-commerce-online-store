<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage\Interfaces;

use App\Storefront\Application\HomePage\Output\HomePageBrandView;
use App\Storefront\Application\HomePage\Output\HomePageProductView;

interface HomePageCatalogQueryInterface
{
    /** @return list<HomePageProductView> */
    public function findNewProducts(int $limit): array;

    /** @return list<HomePageProductView> */
    public function findHitProducts(int $limit): array;

    /** @return list<HomePageBrandView> */
    public function findPopularBrands(int $limit): array;
}
