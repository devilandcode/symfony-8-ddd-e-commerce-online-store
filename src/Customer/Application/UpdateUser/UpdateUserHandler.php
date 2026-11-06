<?php

declare(strict_types=1);

namespace App\Customer\Application\UpdateUser;

use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\Exception\UserNotFound;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(UpdateUser $command): void
    {
        $user = $this->users->findById($command->id)
            ?? throw UserNotFound::withId($command->id);
        $email = mb_strtolower(trim($command->email));
        $existingUser = $this->users->findByEmail($email);

        if (null !== $existingUser && $existingUser->getId() !== $user->getId()) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $email));
        }

        $passwordHash = null;
        if (null !== $command->plainPassword && '' !== $command->plainPassword) {
            if (mb_strlen($command->plainPassword) < 8) {
                throw new \InvalidArgumentException('A password must contain at least 8 characters.');
            }

            $passwordHash = $this->passwordHasher->hash($command->plainPassword);
        }

        $now = new \DateTime();
        $user->updateProfile(
            $command->name,
            $command->surname,
            $email,
            $command->role,
            $command->status,
            $now,
        );

        if (null !== $passwordHash) {
            $user->changePassword($passwordHash, $now);
        }

        $this->users->save($user);
    }
}
