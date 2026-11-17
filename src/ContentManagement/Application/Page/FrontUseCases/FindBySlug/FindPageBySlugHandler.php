<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\FindBySlug;

use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFoundException;
use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;

final readonly class FindPageBySlugHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private PageContentRepositoryInterface $pageContentRepository,
    ) {
    }

    public function __invoke(Slug $slug): ViewPageDTO
    {
        $page = $this->pageRepository->findBySlug($slug);

        if (null === $page) {
            throw PageNotFoundException::withSlug($slug->toString());
        }

        $pageId = $page->getId();
        $pageContent = null === $pageId
            ? null
            : $this->pageContentRepository->findByPageId($pageId);

        return ViewPageDTO::fromPage($page, $pageContent);
    }
}
