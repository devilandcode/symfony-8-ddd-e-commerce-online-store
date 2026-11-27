<?php

declare(strict_types=1);

namespace App\Tests\Cart\Domain;

use App\Cart\Domain\Exception\InvalidCartQuantityException;
use App\Cart\Domain\ValueObject\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    /** @return iterable<string, array{int}> */
    public static function nonPositiveValues(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-3];
    }

    #[DataProvider('nonPositiveValues')]
    public function testQuantityMustBePositive(int $value): void
    {
        $this->expectException(InvalidCartQuantityException::class);

        Quantity::of($value);
    }

    public function testExposesItsValue(): void
    {
        self::assertSame(4, Quantity::of(4)->getValue());
    }

    public function testAddReturnsANewQuantityAndKeepsTheOriginalUnchanged(): void
    {
        $original = Quantity::of(2);

        $sum = $original->add(Quantity::of(3));

        self::assertSame(5, $sum->getValue());
        self::assertSame(2, $original->getValue());
    }

    public function testExceedsComparesWithAUnitCount(): void
    {
        self::assertTrue(Quantity::of(3)->exceeds(2));
        self::assertFalse(Quantity::of(3)->exceeds(3));
        self::assertTrue(Quantity::of(1)->exceeds(0));
    }

    public function testQuantitiesAreComparedByValue(): void
    {
        self::assertTrue(Quantity::of(2)->equals(Quantity::of(2)));
        self::assertFalse(Quantity::of(2)->equals(Quantity::of(3)));
    }
}
