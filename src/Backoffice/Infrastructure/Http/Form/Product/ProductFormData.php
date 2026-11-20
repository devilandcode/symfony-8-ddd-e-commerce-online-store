<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Http\Form\Product;

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
    public ?string $additionalImages = null;
    public ?string $seoTitle = null;
    public ?string $seoDescription = null;
    public int $position = 0;
    public bool $isActive = true;

    /** @var list<ProductCharacteristicFormData> */
    public array $characteristicAssignments = [];
}
