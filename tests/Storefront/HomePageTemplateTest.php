<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
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
        ]));

        self::assertStringContainsString('Summer offers', $html);
        self::assertStringContainsString('Shop now', $html);
        self::assertStringContainsString('NEW ARRIVALS', $html);
        self::assertStringContainsString('BRANDS', $html);
        self::assertStringContainsString('PRICE HITS', $html);
        self::assertStringContainsString('See offers', $html);
        self::assertStringNotContainsString('Hidden slide', $html);
        self::assertStringNotContainsString('Описание 1', $html);
    }

    public function testHomepageTemplateRendersTheFallbackWhenContentIsMissing(): void
    {
        $html = $this->render(PageContentView::empty());

        self::assertStringContainsString('Описание 1', $html);
    }

    private function render(PageContentView $pageContent): string
    {
        self::bootKernel();
        $request = Request::create('/');
        $request->setSession(new Session(new MockArraySessionStorage()));
        static::getContainer()->get('request_stack')->push($request);

        return static::getContainer()->get('twig')->load('index.html.twig')->renderBlock('content', [
            'page' => new ViewPageDTO(
                title: 'Home',
                h1: null,
                desc: null,
                content: null,
                seo_title: 'SEO',
                seo_description: 'Description',
                pageContent: $pageContent,
            ),
        ]);
    }
}
