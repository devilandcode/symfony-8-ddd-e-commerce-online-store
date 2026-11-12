<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\Entity;

use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\ValueObject\ContactsSettings;
use App\StoreSettings\Domain\ValueObject\ContentSettings;
use App\StoreSettings\Domain\ValueObject\MetricsSettings;
use App\StoreSettings\Domain\ValueObject\SettingsData;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'store_settings')]
#[ORM\UniqueConstraint(name: 'UNIQ_STORE_SETTINGS_SECTION_KEY', columns: ['section_key'])]
class SettingSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'section_key', type: 'string', enumType: SettingSectionKey::class, length: 32)]
    private SettingSectionKey $key;

    #[ORM\Column(type: 'json')]
    private array $payload;

    public function __construct(SettingsData $settings)
    {
        $this->key = $settings->sectionKey();
        $this->payload = $settings->toArray();
    }

    public function getKey(): SettingSectionKey
    {
        return $this->key;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function settings(): SettingsData
    {
        return match ($this->key) {
            SettingSectionKey::CONTACTS => ContactsSettings::fromArray($this->payload),
            SettingSectionKey::CONTENT => ContentSettings::fromArray($this->payload),
            SettingSectionKey::METRICS => MetricsSettings::fromArray($this->payload),
        };
    }

    public function replace(SettingsData $settings): void
    {
        if ($settings->sectionKey() !== $this->key) {
            throw new \InvalidArgumentException('Settings data must match the section key.');
        }

        $this->payload = $settings->toArray();
    }
}
