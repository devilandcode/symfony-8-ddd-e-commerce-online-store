<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\Output;

use App\Catalog\Domain\Entity\Brand;

final readonly class BrandViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $h1,
        public ?string $description,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isActive,
    ) {
    }

    public static function fromBrand(Brand $brand): self
    {
        $id = $brand->getId() ?? throw new \LogicException('Persisted brand is missing an id.');

        return new self(
            $id,
            $brand->getTitle(),
            $brand->getSlug()->toString(),
            $brand->getH1(),
            $brand->getDescription(),
            $brand->getSeoTitle(),
            $brand->getSeoDescription(),
            $brand->getPosition(),
            $brand->isActive(),
        );
    }
}
