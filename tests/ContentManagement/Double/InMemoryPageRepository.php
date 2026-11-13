<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Double;

use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;

final class InMemoryPageRepository implements PageRepositoryInterface
{
    /** @var array<int, Page> */
    public array $pages = [];

    public function findAll(): array
    {
        return array_values($this->pages);
    }

    public function findById(int $id): ?Page
    {
        return $this->pages[$id] ?? null;
    }

    public function findBySlug(Slug $slug): ?Page
    {
        foreach ($this->pages as $page) {
            if ($page->getSlug()->toString() === $slug->toString()) {
                return $page;
            }
        }

        return null;
    }

    public function save(Page $page): void
    {
    }

    public function delete(Page $page): void
    {
    }
}
