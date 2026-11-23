<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Input;

use App\Catalog\Domain\ValueObject\Balance;
use App\Catalog\Domain\ValueObject\OldPrice;
use App\Catalog\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\Slug;

final readonly class ProductDataDTO
{
    public function __construct(
        public string $title,
        public Slug $slug,
        public ?string $h1,
        public ?string $description,
        public Price $price,
        public ?OldPrice $oldPrice,
        public Balance $balance,
        public int $categoryId,
        public int $brandId,
        public ?string $image,
        /** @var list<string>|null */
        public ?array $additionalImages,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isNew,
        public bool $isHit,
        public bool $isActive,
        /** @var list<ProductCharacteristicDataDTO> */
        public array $characteristics,
    ) {
    }
}
