<?php

declare(strict_types=1);

namespace App\ContentManagement\Domain\Repository;

use App\ContentManagement\Domain\Entity\Page;
use App\Shared\Domain\ValueObject\Slug;

interface PageRepositoryInterface
{
    /**
     * @return list<Page>
     */
    public function findAll(): array;

    public function findById(int $id): ?Page;

    public function findBySlug(Slug $slug): ?Page;

    public function save(Page $page): void;

    public function delete(Page $page): void;
}
