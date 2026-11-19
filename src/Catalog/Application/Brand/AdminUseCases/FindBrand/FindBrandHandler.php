<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\FindBrand;

use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandNotFoundException;
use App\Catalog\Application\Brand\AdminUseCases\Output\BrandViewDTO;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;

final readonly class FindBrandHandler
{
    public function __construct(private BrandRepositoryInterface $brandRepository)
    {
    }

    public function __invoke(int $id): BrandViewDTO
    {
        $brand = $this->brandRepository->findById($id) ?? throw BrandNotFoundException::withId($id);

        return BrandViewDTO::fromBrand($brand);
    }
}
