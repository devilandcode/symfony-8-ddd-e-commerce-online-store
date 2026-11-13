<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\PageContent\HomePageContentType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PageContentFormTest extends KernelTestCase
{
    public function testHomepageFormCanBeBuiltFromTheDefaultPayload(): void
    {
        self::bootKernel();

        $form = static::getContainer()
            ->get('form.factory')
            ->create(HomePageContentType::class, HomePageContentType::defaultPayload());
        $view = $form->createView();

        self::assertArrayHasKey('slides', $view->children);
        self::assertArrayHasKey('sections', $view->children);
        self::assertArrayHasKey('0', $view->children['slides']->children);
        self::assertArrayHasKey('1', $view->children['slides']->children);
        self::assertArrayHasKey('new_arrivals', $view->children['sections']->children);
        self::assertArrayHasKey('popular_brands', $view->children['sections']->children);
        self::assertArrayHasKey('price_hits', $view->children['sections']->children);
        self::assertSame('Slide 1', $view->children['slides']->children['0']->vars['collection_entry_title']);
        self::assertSame('Slide 2', $view->children['slides']->children['1']->vars['collection_entry_title']);
        self::assertTrue($view->children['slides']->vars['prototype']->vars['collection_entry_prototype']);
        self::assertSame('Slide', $view->children['slides']->vars['prototype']->vars['collection_entry_title_prefix']);
        self::assertSame('__name__', $view->children['slides']->vars['prototype']->vars['collection_entry_index_placeholder']);
        self::assertSame('New arrivals', $view->children['sections']->children['new_arrivals']->vars['collection_entry_title']);
        self::assertSame('Popular brands', $view->children['sections']->children['popular_brands']->vars['collection_entry_title']);
        self::assertSame('Price hits', $view->children['sections']->children['price_hits']->vars['collection_entry_title']);
        self::assertFalse($view->children['sections']->children['new_arrivals']->vars['collection_entry_prototype']);
        foreach (['0', '1'] as $index) {
            self::assertStringNotContainsString('Array', $view->children['slides']->children[$index]->vars['collection_entry_title']);
        }
    }

    public function testFormRejectsAnActiveSlideWithoutAnImageOrAnUnsafeLink(): void
    {
        self::bootKernel();

        $form = static::getContainer()
            ->get('form.factory')
            ->create(HomePageContentType::class, HomePageContentType::defaultPayload(), [
                'csrf_protection' => false,
            ]);
        $form->submit([
            'slides' => [
                [
                    'buttonUrl' => 'javascript:alert(1)',
                    'isActive' => '1',
                ],
                [],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'New arrivals', 'buttonLabel' => '', 'buttonUrl' => ''],
                'popular_brands' => ['heading' => 'Brands', 'buttonLabel' => '', 'buttonUrl' => ''],
                'price_hits' => ['heading' => 'Offers', 'buttonLabel' => '', 'buttonUrl' => ''],
            ],
        ]);

        self::assertFalse($form->isValid());
        self::assertGreaterThan(0, $form->get('slides')->get('0')->getErrors(true)->count());
    }

    public function testSubmittingNoFileKeepsTheStoredImagePath(): void
    {
        self::bootKernel();

        $storedPath = 'uploads/page-content/0123456789abcdef0123456789abcdef.jpg';
        $form = static::getContainer()
            ->get('form.factory')
            ->create(HomePageContentType::class, [
                'slides' => [
                    [
                        'heading' => 'Summer',
                        'description' => '',
                        'buttonLabel' => 'Shop',
                        'buttonUrl' => '/catalog',
                        'desktopImage' => $storedPath,
                        'mobileImage' => null,
                        'isActive' => true,
                    ],
                ],
                'sections' => [
                    'new_arrivals' => ['heading' => 'New arrivals', 'buttonLabel' => '', 'buttonUrl' => ''],
                    'popular_brands' => ['heading' => 'Brands', 'buttonLabel' => '', 'buttonUrl' => ''],
                    'price_hits' => ['heading' => 'Offers', 'buttonLabel' => '', 'buttonUrl' => ''],
                ],
            ], [
                'csrf_protection' => false,
            ]);

        $slideView = $form->createView()->children['slides']->children['0'];
        self::assertSame($storedPath, $slideView->children['desktopImage']->vars['stored_path']);

        $form->submit([
            'slides' => [
                [
                    'heading' => 'Summer',
                    'description' => '',
                    'buttonLabel' => 'Shop',
                    'buttonUrl' => '/catalog',
                    'isActive' => '1',
                ],
            ],
            'sections' => [
                'new_arrivals' => ['heading' => 'New arrivals', 'buttonLabel' => '', 'buttonUrl' => ''],
                'popular_brands' => ['heading' => 'Brands', 'buttonLabel' => '', 'buttonUrl' => ''],
                'price_hits' => ['heading' => 'Offers', 'buttonLabel' => '', 'buttonUrl' => ''],
            ],
        ]);

        self::assertTrue($form->isValid(), (string) $form->getErrors(true));
        self::assertSame($storedPath, $form->getData()['slides'][0]['desktopImage']);
    }
}
