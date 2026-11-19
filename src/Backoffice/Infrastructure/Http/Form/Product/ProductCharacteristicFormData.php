<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Product;

final class ProductCharacteristicFormData
{
    public int $characteristicId = 0;
    public string $title = '';
    public ?string $measure = null;
    public string $value = '';
}
