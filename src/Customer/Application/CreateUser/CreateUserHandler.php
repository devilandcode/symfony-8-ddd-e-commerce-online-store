<?php

declare(strict_types=1);

namespace App\Customer\Application\CreateUser;

use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(CreateUserDTO $userDTO): void
    {
        $email = $userDTO->email->getEmail();

        if (null !== $this->userRepository->findByEmail($email)) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $email));
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
    }
}
