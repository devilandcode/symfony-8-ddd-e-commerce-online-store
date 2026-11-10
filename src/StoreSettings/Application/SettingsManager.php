<?php

declare(strict_types=1);

namespace App\StoreSettings\Application;

use App\Shared\Application\Flusher;
use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use App\StoreSettings\Domain\ValueObject\SettingsData;

final readonly class SettingsManager
{
    public function __construct(
        private SettingSectionRepositoryInterface $settings,
        private SettingsDataFactory $dataFactory,
        private Flusher $flusher,
    ) {
    }

    public function get(SettingSectionKey $key): SettingsData
    {
        return $this->settings->findByKey($key)?->settings()
            ?? $this->dataFactory->defaultsFor($key);
    }

    public function update(SettingsData $settings): void
    {
        $section = $this->settings->findByKey($settings->sectionKey());

        if (null === $section) {
            $section = new SettingSection($settings);
        } else {
            $section->replace($settings);
        }

        $this->settings->save($section);
        $this->flusher->commit();
    }
}
