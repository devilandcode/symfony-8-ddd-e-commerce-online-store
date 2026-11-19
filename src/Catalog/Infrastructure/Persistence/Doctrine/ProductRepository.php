<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('product', 'category', 'brand', 'assignments', 'characteristic')
            ->distinct()
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->join('product.brand', 'brand')
            ->leftJoin('product.characteristicAssignments', 'assignments')
            ->leftJoin('assignments.characteristic', 'characteristic')
            ->orderBy('product.pos', \SortDirection::Ascending)
            ->addOrderBy('product.title', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Product
    {
        $product = $this->entityManager->createQueryBuilder()
            ->select('product', 'category', 'brand', 'assignments', 'characteristic')
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->join('product.brand', 'brand')
            ->leftJoin('product.characteristicAssignments', 'assignments')
            ->leftJoin('assignments.characteristic', 'characteristic')
            ->where('product.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        if (null !== $product && !$product instanceof Product) {
            throw new LogicException('The product repository returned an unexpected entity.');
        }

        return $product;
    }

    public function findBySlug(Slug $slug): ?Product
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug.value' => $slug->toString()]);
        if (null !== $product && !$product instanceof Product) {
            throw new LogicException('The product repository returned an unexpected entity.');
        }

        return $product;
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
    }

    public function delete(Product $product): void
    {
        $this->entityManager->remove($product);
    }
}
