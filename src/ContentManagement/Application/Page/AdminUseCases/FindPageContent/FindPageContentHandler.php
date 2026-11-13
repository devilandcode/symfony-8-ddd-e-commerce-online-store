<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\FindPageContent;

use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\AdminUseCases\Output\PageContentEditorDTO;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class FindPageContentHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private PageContentRepositoryInterface $pageContentRepository,
    ) {
    }

    public function __invoke(int $pageId): PageContentEditorDTO
    {
        $page = $this->pageRepository->findById($pageId) ?? throw PageNotFound::withId($pageId);
        $pageContent = $this->pageContentRepository->findByPageId($pageId);

        return new PageContentEditorDTO(
            $page->getTitle(),
            $page->getSlug()->toString(),
            $pageContent?->payload()->toArray() ?? [],
        );
    }
}
