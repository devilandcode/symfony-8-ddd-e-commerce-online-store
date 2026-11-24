<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\CreateBrand;

use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandSlugAlreadyInUseException;
use App\Catalog\Application\Brand\AdminUseCases\Input\BrandDataDTO;
use App\Catalog\Domain\Entity\Brand;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class CreateBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(BrandDataDTO $data): void
    {
        if (null !== $this->brandRepository->findBySlug($data->slug)) {
            throw BrandSlugAlreadyInUseException::withSlug($data->slug->toString());
        }

        $brand = Brand::create(
            $data->title,
            $data->slug,
            $data->h1,
            $data->description,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
            new \DateTime(),
            $data->image,
        );
        $this->brandRepository->save($brand);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw BrandSlugAlreadyInUseException::withSlug($data->slug->toString(), $exception);
        }
    }
}
