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
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\Storefront\Application\CatalogPage\CatalogPageComposer;
use App\Storefront\Application\CatalogPage\Handler\GetRootCatalogPageHandler;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use App\Storefront\Application\CatalogPage\Interface\CatalogPageQueryInterface;
use App\Storefront\Application\CatalogPage\Output\CatalogProductPage;
use PHPUnit\Framework\TestCase;

final class GetRootCatalogPageHandlerTest extends TestCase
{
    public function testItUsesCategoryIdsAndSkipsCharacteristicFilters(): void
    {
        $filters = new CatalogPageFilters([7], [9], [11 => ['Spray']]);
        $productPage = new CatalogProductPage([], 0, 2, 5, 0);

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'catalog' === $slug->toString()))
            ->willReturn($this->catalogPage());

        $query = $this->createMock(CatalogPageQueryInterface::class);
        $query->expects(self::once())->method('findActiveCategories')->willReturn([]);
        $query->expects(self::once())->method('findActiveBrands')->with(null)->willReturn([]);
        $query->expects(self::never())->method('findFilterableCharacteristics');
        $query->expects(self::once())
            ->method('findProducts')
            ->with(
                null,
                self::callback(static fn (CatalogPageFilters $f): bool => [7] === $f->categoryIds
                    && [9] === $f->brandIds
                    && [] === $f->characteristicValues),
                self::callback(static fn (PageNumber $p): bool => 2 === $p->value),
                5,
            )
            ->willReturn($productPage);

        $page = $this->handler($pageRepository, $query)($filters, new PageNumber(2));

        self::assertSame('Catalog', $page->page->title);
        self::assertNull($page->categorySlug);
        self::assertSame([], $page->characteristics);
        self::assertSame($filters, $page->filters);
        self::assertSame($productPage, $page->productPage);
    }

    private function handler(PageRepositoryInterface $pageRepository, CatalogPageQueryInterface $query): GetRootCatalogPageHandler
    {
        $settingsRepository = $this->createStub(SettingSectionRepositoryInterface::class);
        $settingsRepository->method('findByKey')
            ->willReturn(new SettingSection(new ContentSettings(productsCount: '5')));

        return new GetRootCatalogPageHandler(
            new FindPageBySlugHandler($pageRepository, $this->createStub(PageContentRepositoryInterface::class)),
            new CatalogPageComposer($query, new ListContentSettingHandler($settingsRepository)),
        );
    }

    private function catalogPage(): Page
    {
        return Page::create('Catalog', new Slug('catalog'), null, null, null, null, null, 0, true, new \DateTime('2026-01-01 00:00:00'));
    }
}
