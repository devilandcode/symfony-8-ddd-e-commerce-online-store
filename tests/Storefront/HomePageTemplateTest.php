<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Catalog\Application\HomePage\HomePageBrandView;
use App\Catalog\Application\HomePage\HomePageProductView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\Storefront\Application\HomePage\ViewHomePageCatalogDTO;
use App\Storefront\Application\HomePage\ViewHomePageDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class HomePageTemplateTest extends KernelTestCase
{
    public function testHomepageTemplateReadsStoredSlidesAndSections(): void
    {
        $html = $this->render(new PageContentView([
            'slides' => [
                [
                    'heading' => 'Hidden slide',
                    'description' => '',
                    'buttonLabel' => '',
                    'buttonUrl' => '',
                    'desktopImage' => 'uploads/page-content/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.jpg',
                    'mobileImage' => null,
                    'isActive' => false,
                ],
                [
                    'heading' => 'Summer offers',
                    'description' => 'Explore the collection',
                    'buttonLabel' => 'Shop now',
                    'buttonUrl' => '/catalog',
                    'desktopImage' => 'uploads/page-content/bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb.jpg',
                    'mobileImage' => null,
                    'isActive' => true,
                ],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'NEW ARRIVALS', 'buttonLabel' => 'More', 'buttonUrl' => '/new'],
                'popular_brands' => ['heading' => 'BRANDS', 'buttonLabel' => '', 'buttonUrl' => ''],
                'price_hits' => ['heading' => 'PRICE HITS', 'buttonLabel' => 'See offers', 'buttonUrl' => '/hits'],
            ],
        ]), [
            new HomePageProductView(
                title: 'New product',
                brandTitle: 'New brand',
                image: 'uploads/products/new-product.jpg',
                price: '12.34 EUR',
                oldPrice: null,
                isNew: true,
                isHit: false,
            ),
        ], [
            new HomePageProductView(
                title: 'Hit product',
                brandTitle: 'Hit brand',
                image: null,
                price: '45.67 EUR',
                oldPrice: '50.00 EUR',
                isNew: false,
                isHit: true,
            ),
        ], [
            new HomePageBrandView('Popular brand'),
        ]);

        self::assertStringContainsString('Summer offers', $html);
        self::assertStringContainsString('Shop now', $html);
        self::assertStringContainsString('NEW ARRIVALS', $html);
        self::assertStringContainsString('BRANDS', $html);
        self::assertStringContainsString('PRICE HITS', $html);
        self::assertStringContainsString('See offers', $html);
        self::assertStringContainsString('New product', $html);
        self::assertStringContainsString('12.34 EUR', $html);
        self::assertStringContainsString('Hit product', $html);
        self::assertStringContainsString('50.00 EUR', $html);
        self::assertStringContainsString('Popular brand', $html);
        self::assertStringNotContainsString('Hidden slide', $html);
        self::assertStringNotContainsString('Описание 1', $html);
    }

    public function testHomepageTemplateDoesNotRenderPlaceholderCatalogItemsWhenContentIsMissing(): void
    {
        $html = $this->render(PageContentView::empty());

        self::assertStringNotContainsString('Описание 1', $html);
        self::assertStringNotContainsString('owl-products-more-slider', $html);
        self::assertStringNotContainsString('owl-brands-slider', $html);
    }

    private function render(
        PageContentView $pageContent,
        array $newProducts = [],
        array $hitProducts = [],
        array $brands = [],
    ): string
    {
        self::bootKernel();
        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        static::getContainer()->get('request_stack')->push($request);

        return static::getContainer()->get('twig')->load('index.html.twig')->renderBlock('content', [
            'homePage' => new ViewHomePageDTO(
                page: new ViewPageDTO(
                    title: 'Home',
                    h1: null,
                    desc: null,
                    content: null,
                    seo_title: 'SEO',
                    seo_description: 'Description',
                    pageContent: $pageContent,
                ),
                catalog: new ViewHomePageCatalogDTO($newProducts, $hitProducts, $brands),
            ),
        ]);
    }
}
