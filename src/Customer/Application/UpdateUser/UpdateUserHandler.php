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
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(UpdateUserDTO $userDTO): void
    {
        $user = $this->userRepository->findById($userDTO->id)
            ?? throw UserNotFound::withId($userDTO->id);
        $email = $userDTO->email->getEmail();
        $existingUser = $this->userRepository->findByEmail($email);

        if (null !== $existingUser && $existingUser->getId() !== $user->getId()) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $email));
        }

        $passwordHash = null;
        if (null !== $userDTO->plainPassword && '' !== $userDTO->plainPassword) {
            if (mb_strlen($userDTO->plainPassword) < 8) {
                throw new \InvalidArgumentException('A password must contain at least 8 characters.');
            }

            $passwordHash = $this->passwordHasher->hash($userDTO->plainPassword);
        }

        $now = new \DateTime();
        $user->updateProfile(
            $userDTO->name,
            $userDTO->surname,
            $userDTO->email,
            $userDTO->role,
            $userDTO->status,
            $now,
        );

        if (null !== $passwordHash) {
            $user->changePassword($passwordHash, $now);
        }

        $this->userRepository->save($user);
    }
}
