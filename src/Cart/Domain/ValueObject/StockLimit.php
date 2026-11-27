<?php

declare(strict_types=1);

namespace App\Cart\Domain\ValueObject;

use App\Cart\Domain\Exception\InvalidCartQuantityException;

/**
 * The number of units of a product the cart may hold, as reported by the caller from Catalog stock.
 * It is a value passed into the Cart aggregate; the aggregate never loads or references a Product.
 */
final readonly class StockLimit
{
    private function __construct(private int $units)
    {
    }

    public static function ofUnits(int $units): self
    {
        if ($units < 0) {
            throw new \InvalidArgumentException(sprintf('Stock limit cannot be negative, %d given.', $units));
        }

        return new self($units);
    }

    public function ensureAllows(Quantity $quantity): void
    {
        if ($quantity->exceeds($this->units)) {
            throw InvalidCartQuantityException::limitExceeded($this->units);
        }
    }

    public function getUnits(): int
    {
        return $this->units;
    }
}
