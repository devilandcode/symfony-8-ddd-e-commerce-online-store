<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Application\Product\AdminUseCases\ListProducts\ProductIndexQueryInterface;
use App\Catalog\Application\Product\AdminUseCases\Output\ProductIndexView;
use App\Catalog\Domain\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProductIndexQuery implements ProductIndexQueryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAll(): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'product.id AS id',
                'product.title AS title',
                'product.slug.value AS slug',
                'product.price.minorUnits AS priceMinorUnits',
                'product.price.currencyCode AS currencyCode',
                'product.balance.units AS balance',
                'category.title AS categoryTitle',
                'brand.title AS brandTitle',
                'product.is_active AS isActive',
                'product.pos AS HIDDEN position',
            )
            ->distinct()
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->join('product.brand', 'brand')
            ->orderBy('product.pos', \SortDirection::Ascending)
            ->addOrderBy('product.title', \SortDirection::Ascending)
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $row): ProductIndexView => new ProductIndexView(
                (int) $row['id'],
                (string) $row['title'],
                (string) $row['slug'],
                (int) $row['priceMinorUnits'],
                (string) $row['currencyCode'],
                (int) $row['balance'],
                (string) $row['categoryTitle'],
                (string) $row['brandTitle'],
                (bool) $row['isActive'],
            ),
            $rows,
        );
    }
}
