<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain\ValueObject;

use App\Catalog\Domain\ValueObject\Price;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    public function testPriceStoresMinorUnitsAndNormalizesCurrencyCode(): void
    {
        $price = new Price(12345, ' byn ');

        self::assertSame(12345, $price->getMinorUnits());
        self::assertSame('BYN', $price->getCurrencyCode());
    }

    public function testPriceRejectsNegativeMinorUnits(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Price(-1, 'BYN');
    }

    public function testPriceRejectsMalformedCurrencyCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Price(100, 'BY');
    }
}
