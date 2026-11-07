<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

final readonly class Surname
{
    private ?string $surname;

    public function __construct(?string $surname = null)
    {
        $surname = null === $surname ? null : trim($surname);
        $this->assertSurnameIsValid($surname);
        $this->surname = empty($surname) ? null : $surname;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    private function assertSurnameIsValid(?string $surname): void
    {
        if (null !== $surname && mb_strlen($surname) > 255) {
            throw new \InvalidArgumentException('Surname cannot exceed 255 characters.');
        }
    }
}
