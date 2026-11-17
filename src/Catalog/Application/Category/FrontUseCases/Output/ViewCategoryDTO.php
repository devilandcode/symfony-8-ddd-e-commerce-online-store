<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\FrontUseCases\Output;

use App\Catalog\Domain\Entity\Category;

final readonly class ViewCategoryDTO
{
    public function __construct(
        public string $title,
        public ?string $parentSlug,
        public ?string $parentTitle,
        public ?string $h1,
        public ?string $desc,
        public ?string $content,
        public ?string $seo_title,
        public ?string $seo_description,
    ){}

    public static function fromCategory(Category $category): self
    {
        $parent = $category->getParent();

        return new self(
            title: $category->getTitle(),
            parentSlug: null === $parent ? null : $parent->getSlug()->toString(),
            parentTitle: $parent?->getTitle(),
            h1: $category->getH1(),
            desc: $category->getDesc(),
            content: $category->getContent(),
            seo_title: $category->getSeoTitle(),
            seo_description: $category->getSeoDescription(),
        );
    }
}
