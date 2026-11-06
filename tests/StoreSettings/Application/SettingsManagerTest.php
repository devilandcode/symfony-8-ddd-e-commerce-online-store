<?php

declare(strict_types=1);

namespace App\Tests\StoreSettings\Application;

use App\StoreSettings\Application\SettingsDataFactory;
use App\StoreSettings\Application\SettingsManager;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use PHPUnit\Framework\TestCase;

final class SettingsManagerTest extends TestCase
{
    public function testMissingSettingsReturnAnEmptySection(): void
    {
        $manager = new SettingsManager(new InMemorySettingSectionRepository(), new SettingsDataFactory());

        self::assertSame(
            (new ContentSettings())->toArray(),
            $manager->get(SettingSectionKey::CONTENT)->toArray(),
        );
    }

    public function testUpdateCreatesOrReplacesTheRequestedSection(): void
    {
        $repository = new InMemorySettingSectionRepository();
        $manager = new SettingsManager($repository, new SettingsDataFactory());
        $settings = new ContentSettings('/privacy', '12', '6', '4');

        $manager->update($settings);
        $manager->update(new ContentSettings('/privacy-v2', '15', '8', '5'));

        self::assertSame('/privacy-v2', $manager->get(SettingSectionKey::CONTENT)->toArray()['privacy']);
        self::assertCount(1, $repository->sections);
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
}
