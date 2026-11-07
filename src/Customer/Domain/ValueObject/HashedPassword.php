<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

final readonly class HashedPassword
{
    public function __construct(
        private string $password,
    ) {
        if ('' === $password) {
            throw new \InvalidArgumentException('A password hash cannot be empty.');
        }
    }

    public function getHashedPassword(): string
    {
        return $this->password;
    }
}
