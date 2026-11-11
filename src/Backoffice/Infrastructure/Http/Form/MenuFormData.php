<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

final class MenuFormData
{
    public string $title = '';

    public ?int $parent = null;

    public bool $hasSlug = true;

    public string $slug = '';

    public int $position = 0;

    public bool $isActive = true;
}
