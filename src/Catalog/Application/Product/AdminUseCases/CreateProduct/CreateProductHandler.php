<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\CreateProduct;

use App\Catalog\Application\Product\AdminUseCases\Exception\ProductSlugAlreadyInUseException;
use App\Catalog\Application\Product\AdminUseCases\Input\ProductDataDTO;
use App\Catalog\Application\Product\AdminUseCases\SyncProductCharacteristics;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private BrandRepositoryInterface $brandRepository,
        private SyncProductCharacteristics $syncCharacteristics,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(ProductDataDTO $data): void
    {
        if (null !== $this->productRepository->findBySlug($data->slug)) {
            throw ProductSlugAlreadyInUseException::withSlug($data->slug->toString());
        }

        $category = $this->categoryRepository->findById($data->categoryId)
            ?? throw new \InvalidArgumentException(sprintf('Category with ID %d was not found.', $data->categoryId));
        $brand = $this->brandRepository->findById($data->brandId)
            ?? throw new \InvalidArgumentException(sprintf('Brand with ID %d was not found.', $data->brandId));
        $product = Product::create(
            $data->title,
            $data->slug,
            $data->h1,
            $data->description,
            $data->price,
            $data->oldPrice,
            $data->balance,
            $category,
            $brand,
            $data->image,
            $data->additionalImages,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );
        ($this->syncCharacteristics)($product, $data->characteristics);
        $this->productRepository->save($product);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw ProductSlugAlreadyInUseException::withSlug($data->slug->toString(), $exception);
        }
    }
}
