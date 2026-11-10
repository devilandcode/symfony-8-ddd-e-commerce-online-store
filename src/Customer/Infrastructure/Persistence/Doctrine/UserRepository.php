<?php

namespace App\Customer\Infrastructure\Persistence\Doctrine;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

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

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new UserEmailAlreadyInUse('A user with this email address already exists.', previous: $exception);
        }
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
