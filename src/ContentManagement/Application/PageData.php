<?php

declare(strict_types=1);

namespace App\ContentManagement\Application;

use App\Shared\Domain\ValueObject\Slug;

final readonly class PageData
{
    public function __construct(
        public string $title,
        public Slug $slug,
        public ?string $h1,
        public ?string $description,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
    ) {
    }
}
