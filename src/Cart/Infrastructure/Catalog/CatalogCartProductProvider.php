<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Catalog;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Application\Interface\CartProductProviderInterface;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Reads Catalog data for the cart page with one scalar query, so no Catalog entities cross the boundary.
 * The product is reached through the read-only CartItem::$product association (see the CartItem docblock),
 * joined in the same query, so the stock balance (`max` in the UI) never costs an extra query per line.
 */
final readonly class CatalogCartProductProvider implements CartProductProviderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findActiveInCart(Cart $cart): array
    {
        if (null === $cart->getId()) {
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
                'product.balance.units AS availableUnits',
            )
            ->from(CartItem::class, 'item')
            ->join('item.product', 'product')
            ->join('product.brand', 'brand')
            ->where('item.cart = :cart')
            ->andWhere('product.isActive = true')
            ->setParameter('cart', $cart)
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
                availableUnits: (int) $row['availableUnits'],
            );
        }

        return $products;
    }
}
