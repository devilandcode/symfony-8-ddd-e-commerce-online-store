<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\DeletePage;

use App\ContentManagement\Application\Page\Exception\PageNotFound;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class DeletePageHandler
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    public function __invoke(int $id): void
    {
        $page = $this->pageRepository->findById($id) ?? throw PageNotFound::withId($id);
        $this->pageRepository->delete($page);
    }
}
