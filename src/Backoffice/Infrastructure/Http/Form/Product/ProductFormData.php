<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Product;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductFormData
{
    public string $title = '';
    public string $slug = '';
    public ?string $h1 = null;
    public ?string $description = null;
    public string $price = '';
    public ?string $oldPrice = null;
    public string $currencyCode = 'USD';
    public int $balance = 0;
    public ?int $categoryId = null;
    public ?int $brandId = null;
    public ?string $image = null;
    /** @var list<string>|null */
    public ?array $additionalImages = null;
    public ?UploadedFile $imageUpload = null;
    /** @var list<UploadedFile> */
    public array $additionalImageUploads = [];
    public bool $removeImage = false;
    /** @var list<string> */
    public array $additionalImagesToRemove = [];
    public ?string $seoTitle = null;
    public ?string $seoDescription = null;
    public int $position = 0;
    public bool $isNew = false;
    public bool $isHit = false;
    public bool $isActive = true;

    public ?string $characteristicsCategoryId = null;

    /** @var list<ProductCharacteristicFormData> */
    public array $characteristicAssignments = [];
}
