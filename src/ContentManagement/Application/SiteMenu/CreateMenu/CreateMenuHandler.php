<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\SiteMenu\CreateMenu;

use App\ContentManagement\Application\SiteMenu\Exception\MenuSlugAlreadyInUse;
use App\ContentManagement\Application\SiteMenu\Input\SiteMenuDataDTO;
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

    public function __invoke(SiteMenuDataDTO $date): void
    {
        $slug = $date->slug;
        if (null !== $this->siteMenuRepository->findBySlug($slug)) {
            throw MenuSlugAlreadyInUse::withSlug($slug->toString());
        }

        $siteMenu = SiteMenu::create(
            $date->title,
            $slug,
            $date->header,
            $date->footer,
            $date->position,
            $date->isActive,
            new \DateTime(),
        );

        $this->siteMenuRepository->save($siteMenu);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw MenuSlugAlreadyInUse::withSlug($slug->toString(), $exception);
        }
    }
}
