<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\Input;

use App\Shared\Domain\ValueObject\Slug;

final readonly class SiteMenuDataDTO
{
    public function __construct(
        public string $title,
        public Slug $slug,
        public bool $header,
        public bool $footer,
        public int $position,
        public bool $isActive,
    ) {
    }
}
