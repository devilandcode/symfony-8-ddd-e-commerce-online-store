<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Application\Interface\CartProductProviderInterface;
use App\Catalog\Domain\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

/** Reads Catalog data for the cart page with one scalar query, so no Catalog entities cross the boundary. */
final readonly class CatalogCartProductProvider implements CartProductProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findActiveByIds(array $productIds): array
    {
        if ([] === $productIds) {
            return [];
        }

        $rows = $this->entityManager->createQueryBuilder()
            ->select(
                'product.id AS id',
                'product.title AS title',
                'brand.title AS brandTitle',
                'product.image AS image',
                'product.price.minorUnits AS priceMinorUnits',
                'product.price.currencyCode AS currencyCode',
                'product.oldPrice.minorUnits AS oldPriceMinorUnits',
            )
            ->from(Product::class, 'product')
            ->join('product.brand', 'brand')
            ->where('product.id IN (:ids)')
            ->andWhere('product.isActive = true')
            ->setParameter('ids', $productIds)
            ->getQuery()
            ->getArrayResult();

        $products = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $products[$id] = new CartProductDTO(
                id: $id,
                title: (string) $row['title'],
                brandTitle: (string) $row['brandTitle'],
                image: null === $row['image'] ? null : (string) $row['image'],
                priceMinorUnits: (int) $row['priceMinorUnits'],
                oldPriceMinorUnits: null === $row['oldPriceMinorUnits'] ? null : (int) $row['oldPriceMinorUnits'],
                currencyCode: (string) $row['currencyCode'],
            );
        }

        return $products;
    }
}
