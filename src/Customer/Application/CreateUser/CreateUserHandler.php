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
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(CreateUser $command): void
    {
        $email = mb_strtolower(trim($command->email));

        if (null !== $this->users->findByEmail($email)) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $email));
        }

        if (mb_strlen($command->plainPassword) < 8) {
            throw new \InvalidArgumentException('A password must contain at least 8 characters.');
        }

        $user = User::register(
            $command->name,
            $command->surname,
            $email,
            $this->passwordHasher->hash($command->plainPassword),
            $command->role,
            $command->status,
            new \DateTime(),
        );

        $this->users->save($user);
    }
}
