<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Cart\Domain\ValueObject\CartOwner;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByOwner(CartOwner $owner): ?Cart
    {
        $criteria = $owner->isGuest()
            ? ['guestToken' => $owner->getGuestToken()]
            : ['userId' => $owner->getUserId()];

        return $this->entityManager->getRepository(Cart::class)->findOneBy($criteria);
    }

    public function save(Cart $cart): void
    {
        $this->entityManager->persist($cart);
    }
}
