<?php

namespace App\Customer\Domain\ValueObject;

class Email
{
    private string $email;

    public function __construct(string $email)
    {
        $this->assertEmailIsValid($email);
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return mb_strtolower(trim($this->email));
    }

    private function assertEmailIsValid(string $email): void
    {
       $normalizedEmail = mb_strtolower(trim($email));
       
        if (empty($normalizedEmail)) {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }

        if (!filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }

        if (strlen($normalizedEmail) > 255) {
            throw new \InvalidArgumentException('Email cannot exceed 255 characters.');
        }
    }
}
