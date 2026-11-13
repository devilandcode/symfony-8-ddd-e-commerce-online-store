<?php

declare(strict_types=1);

namespace App\Tests\StoreSettings\Application;

use App\Shared\Application\Flusher;
use App\StoreSettings\Application\SettingsDataFactory;
use App\StoreSettings\Application\AdminUseCases\SettingsManager\SettingsManager;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use PHPUnit\Framework\TestCase;

final class SettingsManagerTest extends TestCase
{
    public function testMissingSettingsReturnAnEmptySection(): void
    {
        $manager = new SettingsManager(new InMemorySettingSectionRepository(), new SettingsDataFactory(), new NullFlusher());

        self::assertSame(
            (new ContentSettings())->toArray(),
            $manager->get(SettingSectionKey::CONTENT)->toArray(),
        );
    }

    public function testUpdateCreatesOrReplacesTheRequestedSection(): void
    {
        $repository = new InMemorySettingSectionRepository();
        $manager = new SettingsManager($repository, new SettingsDataFactory(), new NullFlusher());
        $settings = new ContentSettings(privacy: '/privacy', productsCount: '12', brandsCount: '6', newsCount: '4');

        $manager->update($settings);
        $manager->update(new ContentSettings(privacy: '/privacy-v2', productsCount: '15', brandsCount: '8', newsCount: '5'));

        self::assertSame('/privacy-v2', $manager->get(SettingSectionKey::CONTENT)->toArray()['privacy']);
        self::assertCount(1, $repository->sections);
    }
}

final class NullFlusher implements Flusher
{
    public function commit(): void
    {
    }
}

final class InMemorySettingSectionRepository implements SettingSectionRepositoryInterface
{
    /** @var array<string, SettingSection> */
    public array $sections = [];

    public function findByKey(SettingSectionKey $key): ?SettingSection
    {
        return $this->sections[$key->value] ?? null;
    }

    public function save(SettingSection $section): void
    {
        $this->sections[$section->getKey()->value] = $section;
    }

    public function getContactSetting(): array
    {
        return [];
    }

    public function getContentSetting(): array
    {
        return [];
    }
}
