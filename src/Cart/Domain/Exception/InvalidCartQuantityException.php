<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

final class InvalidCartQuantityException extends \DomainException
{
    public static function mustBePositive(int $quantity): self
    {
        return new self(sprintf('Quantity must be a positive integer, %d given.', $quantity));
    }

    public static function limitExceeded(int $allowed): self
    {
        return new self(sprintf('Cart quantity for a single product cannot exceed the %d unit(s) in stock.', $allowed));
    }
}
