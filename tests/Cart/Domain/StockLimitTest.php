<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\Quantity;
use App\Cart\Domain\ValueObject\StockLimit;
use PHPUnit\Framework\TestCase;

final class StockLimitTest extends TestCase
{
    public function testAllowsQuantityUpToAndIncludingTheLimit(): void
    {
        $limit = StockLimit::ofUnits(3);

        $limit->ensureAllows(Quantity::of(1));
        $limit->ensureAllows(Quantity::of(3));

        $this->addToAssertionCount(1);
    }

    public function testRejectsQuantityAboveTheLimit(): void
    {
        $this->expectException(InvalidCartQuantityException::class);
        $this->expectExceptionMessage('3 unit(s) in stock');

        StockLimit::ofUnits(3)->ensureAllows(Quantity::of(4));
    }

    public function testZeroStockAllowsNothing(): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        StockLimit::ofUnits(0)->ensureAllows(Quantity::of(1));
    }

    public function testLimitCannotBeNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        StockLimit::ofUnits(-1);
    }
}
