<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\FrontUseCases\ListActiveRootMenus;

use App\ContentManagement\Domain\Entity\SiteMenu;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;

final readonly class ListActiveRootMenusHandler
{
    public function __construct(private SiteMenuRepositoryInterface $siteMenuRepository)
    {
    }

    /**
     * @return list<SiteMenu>
     */
    public function __invoke(): array
    {
        return $this->siteMenuRepository->findActiveRoots();
    }
}
