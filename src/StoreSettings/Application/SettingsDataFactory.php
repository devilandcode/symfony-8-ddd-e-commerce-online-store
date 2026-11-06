<?php

declare(strict_types=1);

namespace App\StoreSettings\Application;

use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\ValueObject\ContactsSettings;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\StoreSettings\Domain\ValueObject\MetricsSettings;
use App\StoreSettings\Domain\ValueObject\SettingsData;

final class SettingsDataFactory
{
    public function defaultsFor(SettingSectionKey $key): SettingsData
    {
        return match ($key) {
            SettingSectionKey::CONTACTS => new ContactsSettings(),
            SettingSectionKey::CONTENT => new ContentSettings(),
            SettingSectionKey::METRICS => new MetricsSettings(),
        };
    }

    public function fromArray(SettingSectionKey $key, array $data): SettingsData
    {
        return match ($key) {
            SettingSectionKey::CONTACTS => ContactsSettings::fromArray($data),
            SettingSectionKey::CONTENT => ContentSettings::fromArray($data),
            SettingSectionKey::METRICS => MetricsSettings::fromArray($data),
        };
    }
}
