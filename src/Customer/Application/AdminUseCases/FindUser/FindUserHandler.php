<?php

declare(strict_types=1);

namespace App\Customer\Application\AdminUseCases\FindUser;

use App\Customer\Application\AdminUseCases\Exception\UserNotFound;
use App\Customer\Application\AdminUseCases\Output\UserViewDTO;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class FindUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(int $id): UserViewDTO
    {
        $user = $this->userRepository->findById($id)
            ?? throw UserNotFound::withId($id);

        return UserViewDTO::fromUser($user);
    }
}
