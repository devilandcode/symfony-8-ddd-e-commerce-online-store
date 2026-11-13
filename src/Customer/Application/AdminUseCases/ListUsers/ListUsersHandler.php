<?php

declare(strict_types=1);

namespace App\Customer\Application\AdminUseCases\ListUsers;

use App\Customer\Application\AdminUseCases\Output\UserViewDTO;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class ListUsersHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @return list<UserViewDTO>
     */
    public function __invoke(): array
    {
        return array_map(UserViewDTO::fromUser(...), $this->userRepository->findAll());
    }
}
