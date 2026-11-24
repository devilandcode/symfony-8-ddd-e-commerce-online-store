<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage\Output;

final readonly class ViewHomePageCatalogDTO
{
    /**
     * @param list<HomePageProductView> $newProducts
     * @param list<HomePageProductView> $hitProducts
     * @param list<HomePageBrandView> $brands
     */
    public function __construct(
        public array $newProducts,
        public array $hitProducts,
        public array $brands,
    ) {
    }
}
