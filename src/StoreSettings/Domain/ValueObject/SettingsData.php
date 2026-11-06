<?php

declare(strict_types=1);

namespace App\StoreSettings\Domain\ValueObject;

use App\StoreSettings\Domain\Enum\SettingSectionKey;

interface SettingsData
{
    public function sectionKey(): SettingSectionKey;

    /**
     * @return array<string, string>
     */
    public function toArray(): array;
}
