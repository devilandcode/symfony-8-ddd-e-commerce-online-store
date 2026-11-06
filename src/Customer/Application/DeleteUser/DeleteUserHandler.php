<?php

declare(strict_types=1);

namespace App\Customer\Application\DeleteUser;

use App\Customer\Application\Exception\UserNotFound;
use App\Customer\Domain\Repository\UserRepositoryInterface;

final readonly class DeleteUserHandler
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function __invoke(int $id): void
    {
        $user = $this->users->findById($id)
            ?? throw UserNotFound::withId($id);

        $this->users->delete($user);
    }
}
