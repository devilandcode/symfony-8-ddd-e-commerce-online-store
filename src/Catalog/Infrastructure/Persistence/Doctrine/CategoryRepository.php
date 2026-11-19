<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    /** @return list<Category> */
    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('category', 'parent', 'assignments', 'characteristic')
            ->addSelect('CASE WHEN category.parent IS NULL THEN 0 ELSE 1 END AS HIDDEN parentOrder')
            ->distinct()
            ->from(Category::class, 'category')
            ->leftJoin('category.parent', 'parent')
            ->leftJoin('category.characteristics', 'assignments')
            ->leftJoin('assignments.characteristic', 'characteristic')
            ->orderBy('parentOrder', \SortDirection::Ascending)
            ->addOrderBy('category.pos', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function getRootCategories(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('category', 'children')
            ->distinct()
            ->from(Category::class, 'category')
            ->leftJoin('category.children', 'children', 'WITH', 'children.is_active = true')
            ->where('category.parent IS NULL')
            ->andWhere('category.is_active = true')
            ->orderBy('category.pos', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?Category
    {
        $category = $this->entityManager->createQueryBuilder()
            ->select('category', 'parent', 'assignments', 'characteristic')
            ->from(Category::class, 'category')
            ->leftJoin('category.parent', 'parent')
            ->leftJoin('category.characteristics', 'assignments')
            ->leftJoin('assignments.characteristic', 'characteristic')
            ->where('category.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $category && !$category instanceof Category) {
            throw new \LogicException('The category repository returned an unexpected entity.');
        }

        return $category;
    }

    public function findBySlug(Slug $slug): ?Category
    {
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['slug.value' => $slug->toString()]);

        if (null !== $category && !$category instanceof Category) {
            throw new \LogicException('The category repository returned an unexpected entity.');
        }

        return $category;
    }

    public function hasProducts(Category $category): bool
    {
        return 0 < (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(product.id)')
            ->from(Product::class, 'product')
            ->where('product.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Category $category): void
    {
        $this->entityManager->persist($category);
    }

    public function delete(Category $category): void
    {
        $this->entityManager->remove($category);
    }
}
