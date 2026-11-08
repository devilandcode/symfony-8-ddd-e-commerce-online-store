<?php

declare(strict_types=1);

namespace App\ContentManagement\Application;

use App\ContentManagement\Domain\Entity\Page;

final readonly class PageView
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
            $page->getId() ?? throw new \LogicException('A persisted page must have an ID.'),
            $page->getTitle() ?? throw new \LogicException('A page must have a title.'),
            (string) ($page->getSlug() ?? throw new \LogicException('A page must have a slug.')),
            $page->getH1(),
            $page->getDesc(),
            $page->getContent(),
            $page->getSeoTitle(),
            $page->getSeoDescription(),
            $page->getPos() ?? 0,
            $page->getIsActive() ?? false,
        );
    }
}
