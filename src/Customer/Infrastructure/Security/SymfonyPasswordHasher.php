<?php

namespace App\Customer\Infrastructure\Security;

use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) 
    {}

    public function hash(string $plainPassword, User $user): string
    {
        return $this->hasher->hashPassword($user, $plainPassword);
    }
}
