<?php

namespace App\Customer\Infrastructure\Security;

use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Domain\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private PasswordHasherFactoryInterface $hasherFactory)
    {
    }

    public function hash(string $plainPassword): string
    {
        return $this->hasherFactory->getPasswordHasher(User::class)->hash($plainPassword);
    }
}
