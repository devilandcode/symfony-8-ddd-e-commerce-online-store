<?php

declare(strict_types=1);

namespace App\Customer\Application\FindUser;

use App\Customer\Application\Exception\UserNotFound;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class FindUserHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(int $id): User
    {
        return $this->userRepository->findById($id)
            ?? throw UserNotFound::withId($id);
    }
}
