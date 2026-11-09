<?php

namespace App\ContentManagement\Domain\Repository;

use App\ContentManagement\Domain\Entity\SiteMenu;

interface SiteMenuRepositoryInterface
{
    /**
     * @return list<SiteMenu>
     */
    public function findAll(): array;

    public function findById(int $id): ?SiteMenu;

    public function findBySlug(string $slug): ?SiteMenu;

    public function save(SiteMenu $siteMenu): void;

    public function delete(SiteMenu $siteMenu): void;
}
