<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\ListBrands;

use App\Catalog\Application\Brand\AdminUseCases\Output\BrandViewDTO;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;

final readonly class ListBrandsHandler
{
    public function __construct(private BrandRepositoryInterface $brandRepository)
    {
    }

    /** @return list<BrandViewDTO> */
    public function __invoke(): array
    {
        return array_map(BrandViewDTO::fromBrand(...), $this->brandRepository->findAll());
    }
}
