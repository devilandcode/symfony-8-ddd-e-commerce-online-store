<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Brand;

final class BrandFormData
{
    public string $title = '';
    public string $slug = '';
    public ?string $h1 = null;
    public ?string $description = null;
    public ?string $seoTitle = null;
    public ?string $seoDescription = null;
    public int $position = 0;
    public bool $isActive = true;
}
