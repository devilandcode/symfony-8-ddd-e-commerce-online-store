<?php

namespace App\Customer\Application;

use App\Customer\Domain\Entity\User;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword, User $user): string;
}
