<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\Storefront\Application\HomePage\Output\HomePageBrandView;
use App\Storefront\Application\HomePage\Output\HomePageProductView;
use App\Storefront\Application\HomePage\Output\ViewHomePageCatalogDTO;
use App\Storefront\Application\HomePage\Output\ViewHomePageDTO;
use Imagick;
use ImagickPixel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class HomePageTemplateTest extends KernelTestCase
{
    private string $projectDirectory;
    /** @var list<string> */
    private array $sourceFiles = [];
    /** @var list<string> */
    private array $existingVariantFiles = [];

    protected function setUp(): void
    {
        self::bootKernel();
        $this->projectDirectory = static::getContainer()->getParameter('kernel.project_dir');
        $this->existingVariantFiles = $this->variantFiles();
    }

    protected function tearDown(): void
    {
        foreach ($this->sourceFiles as $sourceFile) {
            if (is_file($sourceFile)) {
                unlink($sourceFile);
            }
        }

        $newVariantFiles = array_diff($this->variantFiles(), $this->existingVariantFiles);
        foreach ($newVariantFiles as $variantFile) {
            if (is_file($variantFile)) {
                unlink($variantFile);
            }
        }

        foreach (array_unique(array_map(dirname(...), $newVariantFiles)) as $directory) {
            if (is_dir($directory)) {
                $items = scandir($directory);
                if (false !== $items && 2 === count($items)) {
                    rmdir($directory);
                }
            }
        }

        parent::tearDown();
    }

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
                    'desktopImage' => $this->createUploadedImage('page-content'),
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
                image: $this->createUploadedImage('products'),
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
            new HomePageBrandView('Popular brand', $this->createUploadedImage('brands')),
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
        self::assertStringContainsString('/uploads/variants/', $html);
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

    private function createUploadedImage(string $folder): string
    {
        $relativePath = sprintf('uploads/%s/test-%s.png', $folder, bin2hex(random_bytes(8)));
        $absolutePath = $this->projectDirectory.'/public/'.$relativePath;
        if (!is_dir(dirname($absolutePath)) && !mkdir(dirname($absolutePath), 0755, true) && !is_dir(dirname($absolutePath))) {
            throw new \RuntimeException('Unable to create a homepage image fixture directory.');
        }

        $image = new Imagick();
        $image->newImage(80, 40, new ImagickPixel('blue'), 'PNG');
        $image->writeImage($absolutePath);
        $image->clear();
        $image->destroy();
        $this->sourceFiles[] = $absolutePath;

        return $relativePath;
    }

    /** @return list<string> */
    private function variantFiles(): array
    {
        $files = glob($this->projectDirectory.'/public/uploads/variants/*/*');

        return false === $files ? [] : $files;
    }
}
