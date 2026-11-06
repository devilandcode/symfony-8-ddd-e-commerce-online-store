<?php

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    public function save(User $user): void;

    public function delete(User $user): void;
}
