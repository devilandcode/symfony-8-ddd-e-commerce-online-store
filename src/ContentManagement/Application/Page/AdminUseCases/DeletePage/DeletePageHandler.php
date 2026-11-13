<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\DeletePage;

use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Application\Flusher;

final readonly class DeletePageHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $page = $this->pageRepository->findById($id) ?? throw PageNotFound::withId($id);
        $this->pageRepository->delete($page);
        $this->flusher->commit();
    }
}
