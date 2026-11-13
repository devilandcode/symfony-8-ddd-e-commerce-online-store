<?php

declare(strict_types=1);

namespace App\Tests\Backoffice;

use App\Backoffice\Infrastructure\Http\Form\PageContent\CollectionEntryTitle;
use App\Backoffice\Infrastructure\Http\Form\PageContent\HomePageContentType;
use PHPUnit\Framework\TestCase;

final class CollectionEntryTitleTest extends TestCase
{
    public function testSlideTitlesUseAOneBasedIndexAndOptionalHeading(): void
    {
        $empty = CollectionEntryTitle::resolve('slides', '0', ['heading' => ''], HomePageContentType::SLIDE_TITLE);
        $named = CollectionEntryTitle::resolve('slides', '1', ['heading' => 'Summer'], HomePageContentType::SLIDE_TITLE);
        $blank = CollectionEntryTitle::resolve('slides', '2', ['heading' => '   '], HomePageContentType::SLIDE_TITLE);

        self::assertSame('Slide 1', $empty->text);
        self::assertSame("Slide 2 \u{2014} Summer", $named->text);
        self::assertSame('Slide 3', $blank->text);
        self::assertStringNotContainsString('Array', $empty->text);
        self::assertStringNotContainsString('Array', $named->text);
    }

    public function testPrototypeKeepsAPlaceholderInsteadOfDumpingTheName(): void
    {
        $title = CollectionEntryTitle::resolve('slides', '__name__', null, HomePageContentType::SLIDE_TITLE);

        self::assertTrue($title->prototype);
        self::assertSame('Slide', $title->text);
        self::assertSame('Slide', $title->prefix);
        self::assertSame('__name__', $title->indexPlaceholder);
        self::assertStringNotContainsString('Array', $title->text);
        self::assertStringNotContainsString('__name__', $title->text);
    }

    public function testSectionTitlesUseTheFormLabelsInsteadOfNumberingTheEntry(): void
    {
        foreach (HomePageContentType::SECTION_LABELS as $key => $label) {
            $title = CollectionEntryTitle::resolve('sections', $key, ['heading' => 'Changed heading'], HomePageContentType::SECTION_LABELS);

            self::assertSame($label, $title->text);
            self::assertFalse($title->prototype);
            self::assertStringNotContainsString('Array', $title->text);
            self::assertStringNotContainsString('Changed heading', $title->text);
        }
    }

    public function testUnconfiguredCollectionsDoNotDumpArrays(): void
    {
        $banner = CollectionEntryTitle::resolve('banners', '0', ['heading' => 'Launch'], null);
        $plain = CollectionEntryTitle::resolve('banners', '1', [], null);
        $named = CollectionEntryTitle::resolve('blocks', 'price_hits', ['heading' => 'Ignored'], null);

        self::assertSame("Banner 1 \u{2014} Launch", $banner->text);
        self::assertSame('Banner 2', $plain->text);
        self::assertSame('Price hits', $named->text);
        self::assertStringNotContainsString('Array', $banner->text);
        self::assertStringNotContainsString('Array', $named->text);
    }
}
