<?php

declare(strict_types=1);

namespace App\Customer\Application\ListUsers;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class ListUsersHandler
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @return list<User>
     */
    public function __invoke(): array
    {
        return $this->users->findAll();
    }
}
