<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\Output;

use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Entity\PageContent;

final readonly class ViewPageDTO
{
    public function __construct(
        public string $title,
        public ?string $h1,
        public ?string $desc,
        public ?string $content,
        public ?string $seo_title,
        public ?string $seo_description,
        public PageContentView $pageContent,
    ) {
    }

    public static function fromPage(Page $page, ?PageContent $pageContent): self
    {
        return new self (
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
