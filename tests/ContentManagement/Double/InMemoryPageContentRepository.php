<?php

declare(strict_types=1);

namespace App\Tests\ContentManagement\Double;

use App\ContentManagement\Domain\Entity\PageContent;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;

final class InMemoryPageContentRepository implements PageContentRepositoryInterface
{
    /** @var array<int, PageContent> */
    private array $items = [];

    public function findByPageId(int $pageId): ?PageContent
    {
        return $this->items[$pageId] ?? null;
    }

    public function save(PageContent $pageContent): void
    {
        $pageId = $pageContent->getPage()->getId();
        if (null === $pageId) {
            throw new \LogicException('Page content cannot be saved without a page id.');
        }

        $this->items[$pageId] = $pageContent;
    }

    public function count(): int
    {
        return \count($this->items);
    }
}
