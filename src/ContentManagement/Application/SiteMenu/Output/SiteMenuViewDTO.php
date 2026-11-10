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
        public bool $header,
        public bool $footer,
        public int $pos,
        public bool $isActive,
    ) {
    }

    public static function fromSiteMenu(SiteMenu $siteMenu): self
    {
        return new self(
            $siteMenu->getId(),
            $siteMenu->getTitle(),
            (string) ($siteMenu->getSlug()),
            $siteMenu->getIsHeader(),
            $siteMenu->getIsFooter(),
            $siteMenu->getPos(),
            $siteMenu->getIsActive(),
        );
    }
}
