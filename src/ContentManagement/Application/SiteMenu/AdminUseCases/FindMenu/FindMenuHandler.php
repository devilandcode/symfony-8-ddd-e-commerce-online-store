<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\FindMenu;

use App\ContentManagement\Application\SiteMenu\AdminUseCases\Exception\MenuNotFound;
use App\ContentManagement\Application\SiteMenu\AdminUseCases\Output\SiteMenuViewDTO;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;

final readonly class FindMenuHandler
{
    public function __construct(private SiteMenuRepositoryInterface $siteMenuRepository)
    {
    }

    public function __invoke(int $id): SiteMenuViewDTO
    {
        $siteMenu = $this->siteMenuRepository->findById($id) ?? throw MenuNotFound::withId($id);

        return SiteMenuViewDTO::fromSiteMenu($siteMenu);
    }
}
