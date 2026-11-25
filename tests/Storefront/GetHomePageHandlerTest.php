<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\Storefront\Application\HomePage\Handler\GetHomePageCatalogHandler;
use App\Storefront\Application\HomePage\Handler\GetHomePageHandler;
use App\Storefront\Application\HomePage\Interface\HomePageCatalogQueryInterface;
use PHPUnit\Framework\TestCase;

final class GetHomePageHandlerTest extends TestCase
{
    public function testItCombinesTheHomePageAndCatalogViews(): void
    {
        $page = Page::create(
            'Home',
            new Slug('home'),
            null,
            null,
            null,
            null,
            null,
            0,
            true,
            new \DateTime('2026-01-01 00:00:00'),
        );
        $pageId = new \ReflectionProperty(Page::class, 'id');
        $pageId->setValue($page, 1);

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'home' === $slug->toString()))
            ->willReturn($page);

        $pageContentRepository = $this->createMock(PageContentRepositoryInterface::class);
        $pageContentRepository->expects(self::once())->method('findByPageId')->with(1)->willReturn(null);

        $settingsRepository = $this->createMock(SettingSectionRepositoryInterface::class);
        $settingsRepository->expects(self::once())
            ->method('findByKey')
            ->with(SettingSectionKey::CONTENT)
            ->willReturn(new SettingSection(new ContentSettings(productsCount: '8', brandsCount: '3')));

        $catalogQuery = $this->createMock(HomePageCatalogQueryInterface::class);
        $catalogQuery->expects(self::once())->method('findNewProducts')->with(8)->willReturn([]);
        $catalogQuery->expects(self::once())->method('findHitProducts')->with(8)->willReturn([]);
        $catalogQuery->expects(self::once())->method('findPopularBrands')->with(3)->willReturn([]);

        $getHomePageCatalog = new GetHomePageCatalogHandler(
            new ListContentSettingHandler($settingsRepository),
            $catalogQuery,
        );
        $handler = new GetHomePageHandler(
            new FindPageBySlugHandler($pageRepository, $pageContentRepository),
            $getHomePageCatalog,
        );

        $homePage = $handler();

        self::assertSame('Home', $homePage->page->title);
        self::assertSame([], $homePage->catalog->newProducts);
        self::assertSame([], $homePage->catalog->hitProducts);
        self::assertSame([], $homePage->catalog->brands);
    }
}
