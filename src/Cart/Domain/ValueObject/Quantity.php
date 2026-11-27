<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

use App\Cart\Domain\Exception\InvalidCartQuantityException;

/** Number of units of one product in a cart line. Always at least one: a line is removed, never set to zero. */
final readonly class Quantity
{
    private function __construct(private int $value)
    {
    }

    public static function of(int $value): self
    {
        if ($value < 1) {
            throw InvalidCartQuantityException::mustBePositive($value);
        }

        return new self($value);
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function exceeds(int $units): bool
    {
        return $this->value > $units;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
