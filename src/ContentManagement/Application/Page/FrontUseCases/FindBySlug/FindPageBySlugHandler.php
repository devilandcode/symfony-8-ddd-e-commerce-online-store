<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\FindBySlug;

use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFound;
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
            throw PageNotFound::withSlug($slug->toString());
        }

        $pageId = $page->getId();
        $pageContent = null === $pageId
            ? null
            : $this->pageContentRepository->findByPageId($pageId);

        return new ViewPageDTO(
            title: $page->getTitle(),
            h1: $page->getH1(),
            desc: $page->getDesc(),
            content: $page->getContent(),
            seo_title: $page->getSeoTitle(),
            seo_description: $page->getSeoDescription(),
            pageContent: new PageContentView($pageContent?->payload()->toArray() ?? []),
        );
    }
}
