<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\Input;

use App\Shared\Domain\ValueObject\Slug;

final readonly class BrandDataDTO
{
    public function __construct(
        public string $title,
        public Slug $slug,
        public ?string $h1,
        public ?string $description,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
        public ?string $image = null,
    ) {
    }
}
