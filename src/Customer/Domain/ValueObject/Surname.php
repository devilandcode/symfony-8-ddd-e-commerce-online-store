<?php

namespace App\Customer\Domain\ValueObject;

final readonly class Surname
{
    private ?string $suraname;

    public function __construct(?string $surname = null)
    {
        $this->assertSurnameIsValid($surname);
        $this->suraname = $surname;
    }

    public function getSurname(): ?string
    {
        return $this->suraname !== null ? trim($this->suraname) : null;
    }

    private function assertSurnameIsValid(?string $surname): void
    {
        if ($surname !== null && strlen(trim($surname)) > 255) {
            throw new \InvalidArgumentException('Surname cannot exceed 255 characters.');
        }
    }
}
