<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Output;

use App\Catalog\Domain\Entity\Category;

final readonly class CategoryViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?int $parentId,
        public ?string $parentTitle,
        public int $level,
        public ?string $h1,
        public ?string $description,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
    ) {
    }

    public static function fromCategory(Category $category): self
    {
        $parent = $category->getParent();
        $id = $category->getId();
        if (null === $id) {
            throw new \LogicException('Persisted category is missing an id.');
        }

        return new self(
            $id,
            $category->getTitle(),
            (string) ($category->getSlug()),
            $parent?->getId(),
            $parent?->getTitle(),
            $category->getLevel(),
            $category->getH1(),
            $category->getDesc(),
            $category->getContent(),
            $category->getSeoTitle(),
            $category->getSeoDescription(),
            $category->getPos(),
            $category->getIsActive(),
        );
    }
}
