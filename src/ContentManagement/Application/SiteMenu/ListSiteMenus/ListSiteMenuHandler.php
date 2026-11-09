<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\ListSiteMenus;

use App\ContentManagement\Application\SiteMenu\Query\SiteMenuView;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;

final readonly class ListSiteMenuHandler
{
    public function __construct(private SiteMenuRepositoryInterface $siteMenuRepository)
    {
    }

    /**
     * @return list<SiteMenuView>
     */
    public function __invoke(): array
    {
        return array_map(SiteMenuView::fromSiteMenu(...), $this->siteMenuRepository->findAll());
    }
}
