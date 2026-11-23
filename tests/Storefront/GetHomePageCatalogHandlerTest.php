<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Catalog\Application\HomePage\HomePageBrandView;
use App\Catalog\Application\HomePage\HomePageCatalogQueryInterface;
use App\Catalog\Application\HomePage\HomePageProductView;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\Storefront\Application\HomePage\GetHomePageCatalogHandler;
use PHPUnit\Framework\TestCase;

final class GetHomePageCatalogHandlerTest extends TestCase
{
    public function testItUsesConfiguredLimitsToLoadHomepageCatalogData(): void
    {
        $newProducts = [
            new HomePageProductView('New', 'Brand', null, '10.00 EUR', null, true, false),
        ];
        $hitProducts = [
            new HomePageProductView('Hit', 'Brand', null, '20.00 EUR', null, false, true),
        ];
        $brands = [new HomePageBrandView('Brand')];

        $settingsRepository = $this->createMock(SettingSectionRepositoryInterface::class);
        $settingsRepository->expects(self::once())
            ->method('findByKey')
            ->with(SettingSectionKey::CONTENT)
            ->willReturn(new SettingSection(new ContentSettings(productsCount: '8', brandsCount: '3')));

        $catalogQuery = $this->createMock(HomePageCatalogQueryInterface::class);
        $catalogQuery->expects(self::once())->method('findNewProducts')->with(8)->willReturn($newProducts);
        $catalogQuery->expects(self::once())->method('findHitProducts')->with(8)->willReturn($hitProducts);
        $catalogQuery->expects(self::once())->method('findPopularBrands')->with(3)->willReturn($brands);

        $handler = new GetHomePageCatalogHandler(
            new ListContentSettingHandler($settingsRepository),
            $catalogQuery,
        );

        $catalog = $handler();

        self::assertSame($newProducts, $catalog->newProducts);
        self::assertSame($hitProducts, $catalog->hitProducts);
        self::assertSame($brands, $catalog->brands);
    }
}
