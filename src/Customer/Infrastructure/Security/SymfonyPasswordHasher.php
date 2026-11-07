<?php

namespace App\Customer\Infrastructure\Security;

use App\Customer\Application\PasswordHasherInterface;
use App\Customer\Application\ValueObject\PlainPassword;
use App\Customer\Domain\Entity\User;
use App\Customer\Domain\ValueObject\HashedPassword;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private PasswordHasherFactoryInterface $hasherFactory)
    {
    }

    public function hash(PlainPassword $plainPassword): HashedPassword
    {
        $hash = $this->hasherFactory->getPasswordHasher(User::class)->hash($plainPassword->getPlainPassword());

        return new HashedPassword($hash);
    }
}
