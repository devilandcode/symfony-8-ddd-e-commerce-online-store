<?php

declare(strict_types=1);

namespace App\Customer\Application\UpdateUser;

use App\Customer\Application\Exception\UserEmailAlreadyInUse;
use App\Customer\Application\Exception\UserNotFound;
use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(UpdateUserDTO $userDTO): void
    {
        $user = $this->userRepository->findById($userDTO->id)
            ?? throw UserNotFound::withId($userDTO->id);
        $existingUser = $this->userRepository->findByEmail($userDTO->email);

        if (null !== $existingUser && $existingUser->getId() !== $user->getId()) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $userDTO->email->getEmail()));
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

        if (null !== $userDTO->plainPassword) {
            $passwordHash = $this->passwordHasher->hash($userDTO->plainPassword);
            $user->changePassword($passwordHash, $now);
        }

        $this->userRepository->save($user);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw new UserEmailAlreadyInUse(sprintf('A user with email "%s" already exists.', $userDTO->email->getEmail()), previous: $exception);
        }
    }
}
