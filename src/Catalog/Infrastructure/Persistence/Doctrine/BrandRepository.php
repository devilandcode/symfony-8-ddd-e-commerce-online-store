<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Brand;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\BrandRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class BrandRepository implements BrandRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('brand')
            ->from(Brand::class, 'brand')
            ->orderBy('brand.pos', \SortDirection::Ascending)
            ->addOrderBy('brand.title', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Brand
    {
        $brand = $this->entityManager->find(Brand::class, $id);
        if (null !== $brand && !$brand instanceof Brand) {
            throw new LogicException('The brand repository returned an unexpected entity.');
        }

        return $brand;
    }

    public function findBySlug(Slug $slug): ?Brand
    {
        $brand = $this->entityManager->getRepository(Brand::class)->findOneBy(['slug.value' => $slug->toString()]);
        if (null !== $brand && !$brand instanceof Brand) {
            throw new LogicException('The brand repository returned an unexpected entity.');
        }

        return $brand;
    }

    public function hasProducts(Brand $brand): bool
    {
        return 0 < (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(product.id)')
            ->from(Product::class, 'product')
            ->where('product.brand = :brand')
            ->setParameter('brand', $brand)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Brand $brand): void
    {
        $this->entityManager->persist($brand);
    }

    public function delete(Brand $brand): void
    {
        $this->entityManager->remove($brand);
    }
}
