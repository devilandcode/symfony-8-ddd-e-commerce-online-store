<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

final readonly class Name
{
    private string $name;

    public function __construct(string $name)
    {
        $name = trim($name);
        $this->assertNameIsValid($name);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function assertNameIsValid(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty.');
        }

        if (mb_strlen($name) > 255) {
            throw new \InvalidArgumentException('Name cannot exceed 255 characters.');
        }
    }
}
