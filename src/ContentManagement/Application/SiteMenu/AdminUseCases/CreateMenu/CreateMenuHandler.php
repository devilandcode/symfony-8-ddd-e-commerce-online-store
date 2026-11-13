<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\AdminUseCases\CreateMenu;

use App\ContentManagement\Application\SiteMenu\AdminUseCases\Exception\MenuSlugAlreadyInUse;
use App\ContentManagement\Application\SiteMenu\AdminUseCases\Input\SiteMenuDataDTO;
use App\ContentManagement\Domain\Entity\SiteMenu;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class CreateMenuHandler
{
    public function __construct(
        private SiteMenuRepositoryInterface $siteMenuRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(SiteMenuDataDTO $data): void
    {
        $parent = $this->parentFromId($data->parentId);

        if (null !== $this->siteMenuRepository->findBySlug($data->slug)) {
            throw MenuSlugAlreadyInUse::withSlug($data->slug->toString());
        }

        $siteMenu = SiteMenu::create(
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
