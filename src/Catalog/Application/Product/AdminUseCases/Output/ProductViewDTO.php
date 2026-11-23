<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Output;

use App\Catalog\Domain\Entity\Product;

final readonly class ProductViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $h1,
        public ?string $description,
        public int $priceMinorUnits,
        public ?int $oldPriceMinorUnits,
        public string $currencyCode,
        public int $balance,
        public int $categoryId,
        public string $categoryTitle,
        public int $brandId,
        public string $brandTitle,
        public ?string $image,
        /** @var list<string>|null */
        public ?array $additionalImages,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public int $position,
        public bool $isNew,
        public bool $isHit,
        public bool $isActive,
        /** @var list<ProductCharacteristicViewDTO> */
        public array $characteristics,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        $id = $product->getId() ?? throw new \LogicException('Persisted product is missing an id.');
        $categoryId = $product->getCategory()->getId() ?? throw new \LogicException('Product category is missing an id.');
        $brandId = $product->getBrand()->getId() ?? throw new \LogicException('Product brand is missing an id.');

        return new self(
            $id,
            $product->getTitle(),
            $product->getSlug()->toString(),
            $product->getH1(),
            $product->getDescription(),
            $product->getPrice()->getMinorUnits(),
            $product->getOldPrice()?->getMinorUnits(),
            $product->getPrice()->getCurrencyCode(),
            $product->getBalance()->getUnits(),
            $categoryId,
            $product->getCategory()->getTitle(),
            $brandId,
            $product->getBrand()->getTitle(),
            $product->getImage(),
            $product->getAdditionalImages(),
            $product->getSeoTitle(),
            $product->getSeoDescription(),
            $product->getPosition(),
            $product->isNew(),
            $product->isHit(),
            $product->isActive(),
            array_map(ProductCharacteristicViewDTO::fromAssignment(...), $product->getCharacteristicAssignments()),
        );
    }
}
