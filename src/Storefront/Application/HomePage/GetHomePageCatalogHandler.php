<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage;

use App\Catalog\Application\HomePage\HomePageCatalogQueryInterface;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;

final readonly class GetHomePageCatalogHandler
{
    public function __construct(
        private ListContentSettingHandler $listContentSetting,
        private HomePageCatalogQueryInterface $homePageCatalogRepository,
    ) {
    }

    public function __invoke(): ViewHomePageCatalogDTO
    {
        $settings = ($this->listContentSetting)();
        $productsLimit = $settings->productsLimit();

        return new ViewHomePageCatalogDTO(
            newProducts: $this->homePageCatalogRepository->findNewProducts($productsLimit),
            hitProducts: $this->homePageCatalogRepository->findHitProducts($productsLimit),
            brands: $this->homePageCatalogRepository->findPopularBrands($settings->brandsLimit()),
        );
    }
}
