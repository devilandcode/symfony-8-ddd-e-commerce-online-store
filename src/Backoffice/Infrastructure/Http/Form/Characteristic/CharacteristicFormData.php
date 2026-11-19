<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Characteristic;

final class CharacteristicFormData
{
    public string $title = '';

    public ?string $measure = null;

    public int $position = 0;

    public bool $isActive = true;
}
