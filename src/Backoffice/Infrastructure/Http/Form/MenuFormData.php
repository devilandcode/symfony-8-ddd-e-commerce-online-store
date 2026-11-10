<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form;

final class MenuFormData
{
    public string $title = '';
    public string $slug = '';
    public bool $header = true;
    public bool $footer = false;
    public int $position = 0;
    public bool $isActive = true;
}
