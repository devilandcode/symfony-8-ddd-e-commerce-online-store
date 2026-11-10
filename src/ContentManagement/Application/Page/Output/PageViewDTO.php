<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\Output;

use App\ContentManagement\Domain\Entity\Page;

final readonly class PageViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $h1,
        public ?string $description,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
    ) {
    }

    public static function fromPage(Page $page): self
    {
        return new self(
            $page->getId(),
            $page->getTitle(),
            (string) ($page->getSlug()),
            $page->getH1(),
            $page->getDesc(),
            $page->getContent(),
            $page->getSeoTitle(),
            $page->getSeoDescription(),
            $page->getPos(),
            $page->getIsActive(),
        );
    }
}
