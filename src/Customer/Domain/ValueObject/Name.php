<?php

namespace App\Customer\Domain\ValueObject;

class Name
{
    private string $name;

    public function __construct(string $name)
    {
        $this->assertNameIsValid($name);
        $this->name = $name;
    }

    public function getName(): string
    {
        return trim($this->name);
    }

    private function assertNameIsValid(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name cannot be empty.');
        }

        if (strlen(trim($name)) > 255) {
            throw new \InvalidArgumentException('Name cannot exceed 255 characters.');
        }
    }
}
