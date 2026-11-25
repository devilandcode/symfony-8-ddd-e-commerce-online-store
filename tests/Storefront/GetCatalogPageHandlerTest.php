<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Catalog\Application\CatalogBrowse\CatalogBrowseQueryInterface;
use App\Catalog\Application\CatalogBrowse\Input\CatalogBrowseCriteria;
use App\Catalog\Application\CatalogBrowse\Output\CatalogProductPage;
use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use App\Storefront\Application\CatalogPage\Handler\GetCatalogPageHandler;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use PHPUnit\Framework\TestCase;

final class GetCatalogPageHandlerTest extends TestCase
{
    public function testRootPageUsesCategoryIdsAndSkipsCharacteristicFilters(): void
    {
        $filters = new CatalogPageFilters(
            categoryIds: [7],
            brandIds: [9],
            characteristicValues: [11 => ['Spray']],
        );
        $productPage = new CatalogProductPage([], 0, 2, 5, 0);

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'catalog' === $slug->toString()))
            ->willReturn($this->catalogPage());

        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepository->expects(self::never())->method('findBySlug');

        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->expects(self::once())->method('findActiveCategories')->willReturn([]);
        $catalogQuery->expects(self::once())->method('findActiveBrands')->with(null)->willReturn([]);
        $catalogQuery->expects(self::never())->method('findFilterableCharacteristics');
        $catalogQuery->expects(self::once())
            ->method('findProducts')
            ->with(
                self::callback(static function (CatalogBrowseCriteria $criteria): bool {
                    return null === $criteria->categorySlug
                        && [7] === $criteria->categoryIds
                        && [9] === $criteria->brandIds
                        && [] === $criteria->characteristicValues;
                }),
                2,
                5,
            )
            ->willReturn($productPage);

        $page = $this->handler($pageRepository, $categoryRepository, $catalogQuery, '5')(null, null, $filters, 2);

        self::assertSame('Catalog', $page->page->title);
        self::assertNull($page->categorySlug);
        self::assertSame([], $page->characteristics);
        self::assertSame($filters, $page->filters);
        self::assertSame($productPage, $page->productPage);
    }

    public function testCategoryPageUsesTheSlugAndDropsCategoryIds(): void
    {
        $filters = new CatalogPageFilters(
            categoryIds: [7],
            brandIds: [9],
            characteristicValues: [11 => ['Spray']],
        );
        $productPage = new CatalogProductPage([], 3, 1, 5, 1);
        $category = Category::create('Face', new Slug('face'), null, null, 0, null, null, null, null, 0, true, new \DateTime());

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::never())->method('findBySlug');

        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'face' === $slug->toString()))
            ->willReturn($category);

        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->expects(self::once())->method('findActiveCategories')->willReturn([]);
        $catalogQuery->expects(self::once())->method('findActiveBrands')->with('face')->willReturn([]);
        $catalogQuery->expects(self::once())->method('findFilterableCharacteristics')->with('face')->willReturn([]);
        $catalogQuery->expects(self::once())
            ->method('findProducts')
            ->with(
                self::callback(static function (CatalogBrowseCriteria $criteria): bool {
                    return 'face' === $criteria->categorySlug
                        && [] === $criteria->categoryIds
                        && [9] === $criteria->brandIds
                        && [11 => ['Spray']] === $criteria->characteristicValues;
                }),
                1,
                5,
            )
            ->willReturn($productPage);

        $page = $this->handler($pageRepository, $categoryRepository, $catalogQuery, '5')('Face', null, $filters, 1);

        self::assertSame('Face', $page->page->title);
        self::assertSame('face', $page->categorySlug);
        self::assertSame($filters, $page->filters);
    }

    public function testChildPageResolvesThroughTheParent(): void
    {
        $now = new \DateTime();
        $parent = Category::create('Skin', new Slug('skin'), null, null, 0, null, null, null, null, 0, true, $now);
        $child = Category::create('Face', new Slug('face'), null, $parent, 1, null, null, null, null, 0, true, $now);
        $filters = new CatalogPageFilters(brandIds: [9]);

        $pageRepository = $this->createMock(PageRepositoryInterface::class);
        $pageRepository->expects(self::never())->method('findBySlug');

        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'face' === $slug->toString()))
            ->willReturn($child);

        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->method('findActiveCategories')->willReturn([]);
        $catalogQuery->method('findActiveBrands')->willReturn([]);
        $catalogQuery->expects(self::once())->method('findFilterableCharacteristics')->with('face')->willReturn([]);
        $catalogQuery->expects(self::once())
            ->method('findProducts')
            ->with(
                self::callback(static fn (CatalogBrowseCriteria $criteria): bool => 'face' === $criteria->categorySlug && [] === $criteria->categoryIds),
                1,
                5,
            )
            ->willReturn(new CatalogProductPage([], 0, 1, 5, 0));

        $page = $this->handler($pageRepository, $categoryRepository, $catalogQuery, '5')('face', 'skin', $filters, 1);

        self::assertSame('Face', $page->page->title);
        self::assertSame('skin', $page->page->parentSlug);
        self::assertSame('face', $page->categorySlug);
    }

    public function testAChildWithADifferentParentIsRejectedBeforeBrowsing(): void
    {
        $now = new \DateTime();
        $parent = Category::create('Skin', new Slug('skin'), null, null, 0, null, null, null, null, 0, true, $now);
        $child = Category::create('Face', new Slug('face'), null, $parent, 1, null, null, null, null, 0, true, $now);

        $categoryRepository = $this->createStub(CategoryRepositoryInterface::class);
        $categoryRepository->method('findBySlug')->willReturn($child);

        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->expects(self::never())->method('findProducts');

        $this->expectException(CategoryNotFoundException::class);

        $this->handler(
            $this->createStub(PageRepositoryInterface::class),
            $categoryRepository,
            $catalogQuery,
            '5',
        )('face', 'other', new CatalogPageFilters(), 1);
    }

    public function testItRejectsAParentSlugWithoutACategorySlug(): void
    {
        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->expects(self::never())->method('findProducts');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler(
            $this->createStub(PageRepositoryInterface::class),
            $this->createStub(CategoryRepositoryInterface::class),
            $catalogQuery,
            '5',
        )(null, 'skin', new CatalogPageFilters(), 1);
    }

    public function testItRejectsPageNumbersBelowOneBeforeReadingSettings(): void
    {
        $settingsRepository = $this->createMock(SettingSectionRepositoryInterface::class);
        $settingsRepository->expects(self::never())->method('findByKey');

        $catalogQuery = $this->createMock(CatalogBrowseQueryInterface::class);
        $catalogQuery->expects(self::never())->method('findProducts');

        $handler = new GetCatalogPageHandler(
            new FindPageBySlugHandler(
                $this->createStub(PageRepositoryInterface::class),
                $this->createStub(PageContentRepositoryInterface::class),
            ),
            new FindCategoryBySlugHandler($this->createStub(CategoryRepositoryInterface::class)),
            new FindChildCategoryHandler($this->createStub(CategoryRepositoryInterface::class)),
            new ListContentSettingHandler($settingsRepository),
            $catalogQuery,
        );

        $this->expectException(\InvalidArgumentException::class);
        $handler(null, null, new CatalogPageFilters(), 0);
    }

    private function handler(
        PageRepositoryInterface $pageRepository,
        CategoryRepositoryInterface $categoryRepository,
        CatalogBrowseQueryInterface $catalogQuery,
        string $productsCount,
    ): GetCatalogPageHandler {
        $settingsRepository = $this->createStub(SettingSectionRepositoryInterface::class);
        $settingsRepository->method('findByKey')
            ->willReturn(new SettingSection(new ContentSettings(productsCount: $productsCount)));

        return new GetCatalogPageHandler(
            new FindPageBySlugHandler($pageRepository, $this->createStub(PageContentRepositoryInterface::class)),
            new FindCategoryBySlugHandler($categoryRepository),
            new FindChildCategoryHandler($categoryRepository),
            new ListContentSettingHandler($settingsRepository),
            $catalogQuery,
        );
    }

    private function catalogPage(): Page
    {
        return Page::create(
            'Catalog',
            new Slug('catalog'),
            null,
            null,
            null,
            null,
            null,
            0,
            true,
            new \DateTime('2026-01-01 00:00:00'),
        );
    }
}
