<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\CreateCategory;

use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Application\Flusher;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $siteMenuRepository,
        private Flusher $flusher,
    ) {
    }
}
