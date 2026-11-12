<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\Repository;

use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;

interface SettingSectionRepositoryInterface
{
    public function findByKey(SettingSectionKey $key): ?SettingSection;

    public function save(SettingSection $section): void;

    public function getContactSetting(): array;

    public function getContentSetting(): array;
}
