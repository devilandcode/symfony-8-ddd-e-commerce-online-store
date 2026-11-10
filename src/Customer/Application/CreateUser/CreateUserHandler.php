<?php

declare(strict_types=1);

namespace App\Customer\Application\CreateUser;

use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(CreateUserDTO $userDTO): void
    {
        if (null !== $this->userRepository->findByEmail($userDTO->email)) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $userDTO->email->getEmail()));
        }

        $user = User::register(
            $userDTO->name,
            $userDTO->surname,
            $userDTO->email,
            $this->passwordHasher->hash($userDTO->plainPassword),
            $userDTO->role,
            $userDTO->status,
            new \DateTime(),
        );

        $this->userRepository->save($user);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $userDTO->email->getEmail()), previous: $exception);
        }
    }
}
