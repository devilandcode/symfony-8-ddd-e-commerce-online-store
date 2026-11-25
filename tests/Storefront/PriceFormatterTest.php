<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Storefront\Infrastructure\Money\PriceFormatter;
use PHPUnit\Framework\TestCase;

final class PriceFormatterTest extends TestCase
{
    public function testItFormatsMinorUnitsUsingTheCurrencyFractionDigits(): void
    {
        $formatter = new PriceFormatter();

        self::assertSame('12.05 USD', $formatter->format(1205, 'USD'));
        self::assertSame('1200 JPY', $formatter->format(1200, 'JPY'));
    }

    public function testItRejectsUnknownCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new PriceFormatter())->format(100, 'XXZ');
    }
}
