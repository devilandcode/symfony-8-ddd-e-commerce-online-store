<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Email
{
    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false, unique: true)]
    private string $email;

    public function __construct(string $email)
    {
        $email = mb_strtolower(trim($email));
        $this->assertEmailIsValid($email);
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    private function assertEmailIsValid(string $email): void
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }

        if (mb_strlen($email) > 255) {
            throw new \InvalidArgumentException('Email cannot exceed 255 characters.');
        }
    }
}
