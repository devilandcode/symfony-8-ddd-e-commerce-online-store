<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\UpdateMenu;

use App\ContentManagement\Application\SiteMenu\AdminUseCases\Exception\MenuSlugAlreadyInUse;
use App\ContentManagement\Application\SiteMenu\AdminUseCases\Input\SiteMenuDataDTO;
use App\ContentManagement\Domain\Entity\SiteMenu;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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

        $parent = $this->parentFromId($data->parentId);

        $existing = $this->siteMenuRepository->findBySlug($data->slug);
        if (null !== $existing && $existing->getId() !== $siteMenu->getId()) {
            throw MenuSlugAlreadyInUse::withSlug($data->slug->toString());
        }

        $siteMenu->update(
            $data->title,
            $parent,
            $data->hasSlug,
            $data->slug,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );

        $this->siteMenuRepository->save($siteMenu);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw MenuSlugAlreadyInUse::withSlug($data->slug->toString(), $exception);
        }
    }

    private function parentFromId(?int $parentId): ?SiteMenu
    {
        if (null === $parentId) {
            return null;
        }

        return $this->siteMenuRepository->findById($parentId)
            ?? throw new \InvalidArgumentException(sprintf('Parent site menu with ID %d was not found.', $parentId));
    }
}
