<?php

namespace App\ContentManagement\Domain\Repository;

use App\ContentManagement\Domain\Entity\SiteMenu;
use App\Shared\Domain\ValueObject\Slug;

interface SiteMenuRepositoryInterface
{
    /**
     * @return list<SiteMenu>
     */
    public function findAll(): array;

    public function findById(int $id): ?SiteMenu;

    public function findBySlug(Slug $slug): ?SiteMenu;

    public function save(SiteMenu $siteMenu): void;

    public function delete(SiteMenu $siteMenu): void;
}
