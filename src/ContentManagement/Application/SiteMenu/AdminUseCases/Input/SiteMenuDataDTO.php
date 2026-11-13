<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\Input;

use App\Shared\Domain\ValueObject\Slug;

final readonly class SiteMenuDataDTO
{
    public function __construct(
        public string $title,
        public ?int $parentId,
        public bool $hasSlug,
        public Slug $slug,
        public int $position,
        public bool $isActive,
    ) {
    }
}
