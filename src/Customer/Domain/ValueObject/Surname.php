<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Surname
{
    #[ORM\Column(name: 'surname', type: 'string', length: 255, nullable: true)]
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
