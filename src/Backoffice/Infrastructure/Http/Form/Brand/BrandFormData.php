<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Brand;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    public ?string $image = null;
    public ?UploadedFile $imageUpload = null;
    public bool $removeImage = false;
}
