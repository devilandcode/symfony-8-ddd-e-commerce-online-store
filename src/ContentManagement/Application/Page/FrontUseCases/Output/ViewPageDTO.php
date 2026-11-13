<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FrontUseCases\Output;

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
}
