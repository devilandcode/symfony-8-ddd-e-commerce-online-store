<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

final readonly class PlainPassword
{
    public function __construct(
        private string $password,
    ) {
        if (mb_strlen($password) < 8) {
            throw new \InvalidArgumentException('A password must contain at least 8 characters.');
        }
    }    

    public function getPlainPassword(): string
    {
        return $this->password;
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->password);
    }
}
