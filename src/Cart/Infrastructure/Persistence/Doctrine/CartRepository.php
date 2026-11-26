<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Cart::class)->findAll();
    }

    public function findByHash(string $hash): array
    {
        return $this->entityManager->getRepository(Cart::class)->findBy(['hash' => $hash]);
    }

    public function findByUserId(int $userId): array
    {
        return $this->entityManager->getRepository(Cart::class)->findBy(['user' => $userId]);
    }

    public function save(Cart $cart): void
    {
        $this->entityManager->persist($cart);
    }

    public function remove(Cart $cart): void
    {
        $this->entityManager->remove($cart);
    }
}
