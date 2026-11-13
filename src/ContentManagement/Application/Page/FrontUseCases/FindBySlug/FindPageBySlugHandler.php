<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\FindBySlug;

use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\ContentManagement\Infrastructure\Persistence\Doctrine\PageRepository;
use App\Shared\Domain\ValueObject\Slug;

final readonly class FindPageBySlugHandler
{
    public function __construct(private PageRepository $pageRepository)
    {}


    public function __invoke(Slug $slug): ?ViewPageDTO
    {
        $page = $this->pageRepository->findBySlug($slug);

        if (null === $page) {
            return throw PageNotFound::withSlug($slug->toString());
        }

        return new ViewPageDTO(
            title: $page->getTitle(),
            h1: $page->getH1(),
            desc: $page->getDesc(),
            content: $page->getContent(),
            seo_title: $page->getSeoTitle(),
            seo_description: $page->getSeoDescription(),
        );
    }

}
