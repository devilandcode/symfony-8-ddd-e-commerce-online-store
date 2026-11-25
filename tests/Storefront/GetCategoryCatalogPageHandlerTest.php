<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\Catalog\Application\Category\FrontUseCases\FindBySlug\FindCategoryBySlugHandler;
use App\Catalog\Application\Category\FrontUseCases\FindChild\FindChildCategoryHandler;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use App\StoreSettings\Application\FrontUseCases\ListContentSetting\ListContentSettingHandler;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\Storefront\Application\CatalogPage\CatalogPageComposer;
use App\Storefront\Application\CatalogPage\Handler\GetCategoryCatalogPageHandler;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use App\Storefront\Application\CatalogPage\Interface\CatalogPageQueryInterface;
use App\Storefront\Application\CatalogPage\Output\CatalogProductPage;
use PHPUnit\Framework\TestCase;

final class GetCategoryCatalogPageHandlerTest extends TestCase
{
    public function testTopLevelCategoryUsesTheSlugAndDropsCategoryIds(): void
    {
        $filters = new CatalogPageFilters([7], [9], [11 => ['Spray']]);
        $productPage = new CatalogProductPage([], 3, 1, 5, 1);

        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepository->expects(self::once())
            ->method('findBySlug')
            ->with(self::callback(static fn (Slug $slug): bool => 'face' === $slug->toString()))
            ->willReturn(Category::create('Face', new Slug('face'), null, null, 0, null, null, null, null, 0, true, new \DateTime()));

        $query = $this->createMock(CatalogPageQueryInterface::class);
        $query->expects(self::once())->method('findActiveCategories')->willReturn([]);
        $query->expects(self::once())->method('findActiveBrands')->with('face')->willReturn([]);
        $query->expects(self::once())->method('findFilterableCharacteristics')->with('face')->willReturn([]);
        $query->expects(self::once())
            ->method('findProducts')
            ->with(
                'face',
                self::callback(static fn (CatalogPageFilters $f): bool => [] === $f->categoryIds
                    && [9] === $f->brandIds
                    && [11 => ['Spray']] === $f->characteristicValues),
                self::callback(static fn (PageNumber $p): bool => 1 === $p->value),
                5,
            )
            ->willReturn($productPage);

        $page = $this->handler($categoryRepository, $query)(null, 'face', $filters, new PageNumber());

        self::assertSame('Face', $page->page->title);
        self::assertSame('face', $page->categorySlug);
        self::assertSame($filters, $page->filters);
        self::assertSame($productPage, $page->productPage);
    }

    public function testChildCategoryResolvesThroughTheParent(): void
    {
        [$parent, $child] = $this->categories();

        $categoryRepository = $this->createStub(CategoryRepositoryInterface::class);
        $categoryRepository->method('findBySlug')->willReturn($child);

        $query = $this->createStub(CatalogPageQueryInterface::class);
        $query->method('findProducts')->willReturn(new CatalogProductPage([], 0, 1, 5, 0));

        $page = $this->handler($categoryRepository, $query)('skin', 'face', new CatalogPageFilters(), new PageNumber());

        self::assertSame('Face', $page->page->title);
        self::assertSame('skin', $page->page->parentSlug);
        self::assertSame('face', $page->categorySlug);
    }

    public function testAChildWithADifferentParentIsRejectedBeforeBrowsing(): void
    {
        [, $child] = $this->categories();

        $categoryRepository = $this->createStub(CategoryRepositoryInterface::class);
        $categoryRepository->method('findBySlug')->willReturn($child);

        $query = $this->createMock(CatalogPageQueryInterface::class);
        $query->expects(self::never())->method('findProducts');

        $this->expectException(CategoryNotFoundException::class);

        $this->handler($categoryRepository, $query)('other', 'face', new CatalogPageFilters(), new PageNumber());
    }

    /** @return array{0: Category, 1: Category} */
    private function categories(): array
    {
        $now = new \DateTime();
        $parent = Category::create('Skin', new Slug('skin'), null, null, 0, null, null, null, null, 0, true, $now);

        return [$parent, Category::create('Face', new Slug('face'), null, $parent, 1, null, null, null, null, 0, true, $now)];
    }

    private function handler(CategoryRepositoryInterface $categoryRepository, CatalogPageQueryInterface $query): GetCategoryCatalogPageHandler
    {
        $settingsRepository = $this->createStub(SettingSectionRepositoryInterface::class);
        $settingsRepository->method('findByKey')
            ->willReturn(new SettingSection(new ContentSettings(productsCount: '5')));

        return new GetCategoryCatalogPageHandler(
            new FindCategoryBySlugHandler($categoryRepository),
            new FindChildCategoryHandler($categoryRepository),
            new CatalogPageComposer($query, new ListContentSettingHandler($settingsRepository)),
        );
    }
}
