<?php

namespace App\Customer\Domain\Repository;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\ValueObject\Email;

interface UserRepositoryInterface
{
    /**
     * @return list<User>
     */
    public function findAll(): array;

    public function findByEmail(Email $email): ?User;

    public function findById(int $id): ?User;

    public function save(User $user): void;

    public function delete(User $user): void;
}
