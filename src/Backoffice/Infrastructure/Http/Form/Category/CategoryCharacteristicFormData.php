<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Category;

final class CategoryCharacteristicFormData
{
    public int $characteristicId = 0;

    public string $title = '';

    public ?string $measure = null;

    public bool $assigned = false;

    public bool $isActive = true;

    public bool $inFilter = false;
}
