<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\DeleteMenu;

use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Application\Flusher;

final readonly class DeleteMenuHandler
{
    public function __construct(
        private SiteMenuRepositoryInterface $siteMenuRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $menu = $this->siteMenuRepository->findById($id);

        if (!$menu) {
            throw new \InvalidArgumentException(sprintf('Menu with ID %d not found.', $id));
        }

        $this->siteMenuRepository->delete($menu);
        $this->flusher->commit();
    }
}
