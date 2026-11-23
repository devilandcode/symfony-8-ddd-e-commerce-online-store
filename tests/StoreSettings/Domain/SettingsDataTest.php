<?php

declare(strict_types=1);

namespace App\Tests\StoreSettings\Domain;

use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\ValueObject\ContactsSettings;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use PHPUnit\Framework\TestCase;

final class SettingsDataTest extends TestCase
{
    public function testSettingsCanBeRestoredFromTheirPersistedPayload(): void
    {
        $contacts = ContactsSettings::fromArray([
            'copyright' => '© Store',
            'email' => 'orders@example.com',
            'company_info' => '<p>Company</p>',
            'footer_shop_1' => 'Location 1',
            'footer_shop_2' => 'Location 2',
            'instagram' => 'https://instagram.com/store',
            'facebook' => 'https://facebook.com/store',
        ]);

        self::assertSame('contacts', $contacts->sectionKey()->value);
        self::assertSame('© Store', $contacts->toArray()['copyright']);
        self::assertSame($contacts->toArray(), (new SettingSection($contacts))->settings()->toArray());
    }

    public function testSettingsRejectValuesThatDoNotMatchTheirDeclaredType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ContentSettings::fromArray(['products_count' => 12]);
    }

    public function testHomepageLimitsTreatEmptyValuesAsZeroAndParseNonNegativeIntegers(): void
    {
        $emptySettings = new ContentSettings();
        $settings = new ContentSettings(productsCount: '0012', brandsCount: ' 6 ');

        self::assertSame(0, $emptySettings->productsLimit());
        self::assertSame(0, $emptySettings->brandsLimit());
        self::assertSame(12, $settings->productsLimit());
        self::assertSame(6, $settings->brandsLimit());
    }

    public function testHomepageLimitsRejectMalformedValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ContentSettings(productsCount: '-1'))->productsLimit();
    }

    public function testSectionRejectsSettingsForAnotherSection(): void
    {
        $section = new SettingSection(new ContactsSettings());

        $this->expectException(\InvalidArgumentException::class);
        $section->replace(new ContentSettings());
    }
}
