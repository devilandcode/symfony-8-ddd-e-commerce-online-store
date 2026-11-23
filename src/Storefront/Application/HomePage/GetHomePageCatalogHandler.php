<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage;

use App\Catalog\Application\HomePage\HomePageCatalogQueryInterface;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;

final readonly class GetHomePageCatalogHandler
{
    public function __construct(
        private ListContentSettingHandler $listContentSetting,
        private HomePageCatalogQueryInterface $homePageCatalogQuery,
    ) {
    }

    public function __invoke(): ViewHomePageCatalogDTO
    {
        $settings = ($this->listContentSetting)();
        $productsLimit = $settings->productsLimit();

        return new ViewHomePageCatalogDTO(
            newProducts: $this->homePageCatalogQuery->findNewProducts($productsLimit),
            hitProducts: $this->homePageCatalogQuery->findHitProducts($productsLimit),
            brands: $this->homePageCatalogQuery->findPopularBrands($settings->brandsLimit()),
        );
    }
}
