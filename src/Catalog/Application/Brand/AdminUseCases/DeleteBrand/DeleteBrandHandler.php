<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\DeleteBrand;

use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandHasProductsException;
use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandNotFoundException;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class DeleteBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $brand = $this->brandRepository->findById($id) ?? throw BrandNotFoundException::withId($id);
        if ($this->brandRepository->hasProducts($brand)) {
            throw BrandHasProductsException::withId($id);
        }

        $this->brandRepository->delete($brand);
        $this->flusher->commit();
    }
}
