<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\Output;

use App\ContentManagement\Domain\Entity\SiteMenu;

final readonly class SiteMenuViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?int $parentId,
        public ?string $parentTitle,
        public bool $hasSlug,
        public int $position,
        public bool $isActive,
    ) {
    }

    public static function fromSiteMenu(SiteMenu $siteMenu): self
    {
        $parent = $siteMenu->getParent();
        $id = $siteMenu->getId();
        if (null === $id) {
            throw new \LogicException('Persisted site menu is missing an id.');
        }

        return new self(
            $id,
            $siteMenu->getTitle(),
            $siteMenu->getSlug()->toString(),
            $parent?->getId(),
            $parent?->getTitle(),
            $siteMenu->hasSlug(),
            $siteMenu->getPos(),
            $siteMenu->getIsActive(),
        );
    }
}
