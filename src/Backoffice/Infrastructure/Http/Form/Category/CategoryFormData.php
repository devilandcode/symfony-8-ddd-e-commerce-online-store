<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Category;

final class CategoryFormData
{
    public string $title = '';

    public string $slug = '';

    public ?string $h1 = null;

    public ?int $parent = null;

    public int $level = 0;

    public ?string $description = null;

    public ?string $content = null;

    public ?string $seoTitle = null;

    public ?string $seoDescription = null;

    public int $position = 0;

    public bool $isActive = true;
}
