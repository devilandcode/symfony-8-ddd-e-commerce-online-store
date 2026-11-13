<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\SavePageContent;

use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Domain\Entity\PageContent;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\ContentManagement\Domain\ValueObject\PageContentData;
use App\Shared\Application\Flusher;

final readonly class SavePageContentHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private PageContentRepositoryInterface $pageContentRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(int $pageId, PageContentData $payload): void
    {
        $page = $this->pageRepository->findById($pageId) ?? throw PageNotFound::withId($pageId);
        $pageContent = $this->pageContentRepository->findByPageId($pageId);

        if (null === $pageContent) {
            $pageContent = PageContent::create($page, $payload);
        } else {
            $pageContent->update($payload);
        }

        $this->pageContentRepository->save($pageContent);
        $this->flusher->commit();
    }
}
