<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Entity\CartItem;
use App\Catalog\Domain\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persistence glue for the read-only CartItem -> Product association (see the CartItem docblock).
 *
 * The cart domain only knows the product id, but Doctrine writes the product_id column through the association,
 * so a lazy reference (no query) is attached right before a new line is inserted. No business decision is made here.
 */
#[AsEntityListener(event: Events::prePersist, entity: CartItem::class)]
final readonly class CartItemProductLinker
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(CartItem $item): void
    {
        (new \ReflectionProperty(CartItem::class, 'product'))
            ->setValue($item, $this->entityManager->getReference(Product::class, $item->getProductId()));
    }
}
