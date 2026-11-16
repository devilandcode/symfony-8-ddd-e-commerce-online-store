<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Input;

use App\Shared\Domain\ValueObject\Slug;

final readonly class CategoryDataDTO
{
    public function __construct(
        public string $title,
        public Slug $slug,
        public ?string $h1,
        public ?int $parentId,
        public int $level,
        public ?string $desc,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
    ) {
    }
}
