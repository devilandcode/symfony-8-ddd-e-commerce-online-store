<?php

declare(strict_types=1);

namespace App\Catalog\Application\Brand\AdminUseCases\UpdateBrand;

use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandNotFoundException;
use App\Catalog\Application\Brand\AdminUseCases\Exception\BrandSlugAlreadyInUseException;
use App\Catalog\Application\Brand\AdminUseCases\Input\BrandDataDTO;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdateBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id, BrandDataDTO $data): void
    {
        $brand = $this->brandRepository->findById($id) ?? throw BrandNotFoundException::withId($id);
        $existing = $this->brandRepository->findBySlug($data->slug);
        if (null !== $existing && $existing->getId() !== $brand->getId()) {
            throw BrandSlugAlreadyInUseException::withSlug($data->slug->toString());
        }

        $brand->update(
            $data->title,
            $data->slug,
            $data->h1,
            $data->description,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );
        $this->brandRepository->save($brand);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw BrandSlugAlreadyInUseException::withSlug($data->slug->toString(), $exception);
        }
    }
}
