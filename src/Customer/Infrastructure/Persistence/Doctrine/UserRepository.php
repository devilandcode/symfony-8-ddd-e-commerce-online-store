<?php

namespace App\Customer\Infrastructure\Persistence\Doctrine;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Customer\Domain\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function findByEmail(Email $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email.email' => $email->getEmail(),
        ]);

        if (null !== $user && !$user instanceof User) {
            throw new \LogicException('The user repository returned an unexpected entity.');
        }

        return $user;
    }

    public function findAll(): array
    {
        /** @var list<User> $users */
        $users = $this->entityManager->getRepository(User::class)->findBy([], ['id' => 'ASC']);

        return $users;
    }

    public function findById(int $id): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (null !== $user && !$user instanceof User) {
            throw new \LogicException('The user repository returned an unexpected entity.');
        }

        return $user;
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
    }
}
