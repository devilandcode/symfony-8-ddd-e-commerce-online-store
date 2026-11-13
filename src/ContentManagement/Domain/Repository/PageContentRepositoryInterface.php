<?php

declare(strict_types=1);

namespace App\ContentManagement\Domain\Repository;

use App\ContentManagement\Domain\Entity\PageContent;

interface PageContentRepositoryInterface
{
    public function findByPageId(int $pageId): ?PageContent;

    public function save(PageContent $pageContent): void;
}
