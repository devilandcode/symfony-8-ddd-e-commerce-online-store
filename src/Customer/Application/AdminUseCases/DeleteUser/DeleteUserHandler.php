<?php

declare(strict_types=1);

namespace App\Customer\Application\AdminUseCases\DeleteUser;

use App\Customer\Application\AdminUseCases\Exception\UserNotFoundException;
use App\Customer\Domain\Repository\UserRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $user = $this->userRepository->findById($id)
            ?? throw UserNotFoundException::withId($id);

        $this->userRepository->delete($user);
        $this->flusher->commit();
    }
}
