<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Storefront\Application\CatalogPage\Output\CatalogBrandView;
use App\Storefront\Application\CatalogPage\Output\CatalogCategoryView;
use App\Storefront\Application\CatalogPage\Output\CatalogCharacteristicFilterView;
use App\Storefront\Application\CatalogPage\Output\CatalogProductPage;
use App\Storefront\Application\CatalogPage\Output\CatalogProductView;
use App\Catalog\Application\Category\FrontUseCases\Output\ViewCategoryDTO;
use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Output\ViewCatalogPageDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CatalogPageTemplateTest extends KernelTestCase
{
    public function testItRendersCatalogFiltersAndProductResults(): void
    {
        self::bootKernel();
        $request = Request::create('/catalog?categories%5B%5D=7&brands%5B%5D=9');
        $request->attributes->set('_route', 'catalog');
        $request->attributes->set('_route_params', []);
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        try {
            $html = static::getContainer()->get('twig')
                ->load('catalog.html.twig')
                ->renderBlock('content', [
                    'catalog' => new ViewCatalogPageDTO(
                        page: new ViewPageDTO(
                            title: 'Catalog',
                            h1: null,
                            desc: null,
                            content: null,
                            seo_title: null,
                            seo_description: null,
                            pageContent: PageContentView::empty(),
                        ),
                        categorySlug: null,
                        categories: [new CatalogCategoryView(7, 'Care', 'care', null)],
                        brands: [new CatalogBrandView(9, 'Brand')],
                        characteristics: [],
                        productPage: new CatalogProductPage(
                            products: [new CatalogProductView('Product', 'Brand', null, '10.00 EUR', null, false, false)],
                            totalProducts: 1,
                            currentPage: 1,
                            pageSize: 5,
                            totalPages: 1,
                        ),
                        filters: new CatalogPageFilters([7], [9]),
                    ),
                ]);
        } finally {
            $requestStack->pop();
        }

        self::assertStringContainsString('Care', $html);
        self::assertStringContainsString('Brand', $html);
        self::assertStringContainsString('Product', $html);
        self::assertStringContainsString('10.00 EUR', $html);
        self::assertStringContainsString('name="categories[]"', $html);
        self::assertStringContainsString('name="brands[]"', $html);
        self::assertStringContainsString('checked', $html);
    }

    public function testItPreservesCategoryFiltersInPaginationLinks(): void
    {
        self::bootKernel();
        $request = Request::create('/catalog/skin/face?brands%5B%5D=9&characteristics%5B11%5D%5B%5D=Spray&page=2');
        $request->attributes->set('_route', 'level2');
        $request->attributes->set('_route_params', ['parentSlug' => 'skin', 'slug' => 'face']);
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        try {
            $html = static::getContainer()->get('twig')
                ->load('catalog.html.twig')
                ->renderBlock('content', [
                    'catalog' => new ViewCatalogPageDTO(
                        page: new ViewCategoryDTO('Face care', 'skin', 'Skin', null, null, null, null, null),
                        categorySlug: 'face',
                        categories: [
                            new CatalogCategoryView(1, 'Skin', 'skin', null),
                            new CatalogCategoryView(2, 'Face', 'face', 'skin'),
                        ],
                        brands: [new CatalogBrandView(9, 'Brand')],
                        characteristics: [
                            new CatalogCharacteristicFilterView(11, 'Type', null, ['Spray']),
                        ],
                        productPage: new CatalogProductPage(
                            products: [new CatalogProductView('Product', 'Brand', null, '10.00 EUR', null, false, false)],
                            totalProducts: 12,
                            currentPage: 2,
                            pageSize: 5,
                            totalPages: 3,
                        ),
                        filters: new CatalogPageFilters([], [9], [11 => ['Spray']]),
                    ),
                ]);
        } finally {
            $requestStack->pop();
        }

        self::assertStringContainsString('name="characteristics[11][]"', $html);
        self::assertStringContainsString('Spray', $html);
        self::assertStringContainsString('page=1', $html);
        self::assertStringContainsString('brands%5B', $html);
        self::assertStringContainsString('characteristics%5B11%5D', $html);
    }
}
