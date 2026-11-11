<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\UpdateMenu;

use App\ContentManagement\Application\SiteMenu\Input\SiteMenuDataDTO;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Application\Flusher;

final readonly class UpdateMenuHandler
{
    public function __construct(
        private SiteMenuRepositoryInterface $siteMenuRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(int $id, SiteMenuDataDTO $data): void
    {
        $siteMenu = $this->siteMenuRepository->findById($id);

        if (null === $siteMenu) {
            throw new \InvalidArgumentException(sprintf('Site menu with ID %d not found.', $id));
        }

        $siteMenu->update(
            $data->title,
            $data->slug,
            $data->header,
            $data->footer,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );

        $this->siteMenuRepository->save($siteMenu);
        $this->flusher->commit();
    }
}
