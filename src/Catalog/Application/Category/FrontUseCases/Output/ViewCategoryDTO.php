<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\FrontUseCases\Output;

final readonly class ViewCategoryDTO
{
    public function __construct(
        public string $title,
        public ?string $h1,
        public ?string $desc,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
    ){}
}
