<?php

declare(strict_types=1);

namespace App\Tests\Backoffice\Money;

use App\Backoffice\Infrastructure\Money\CurrencyAmountConverter;
use PHPUnit\Framework\TestCase;

final class CurrencyAmountConverterTest extends TestCase
{
    public function testConvertsTwoFractionDigitCurrencyWithoutFloatingPoint(): void
    {
        $converter = new CurrencyAmountConverter();

        self::assertSame(12345, $converter->toMinorUnits('123.45', 'BYN'));
        self::assertSame('123.45', $converter->fromMinorUnits(12345, 'BYN'));
    }

    public function testConvertsCurrenciesWithZeroAndThreeFractionDigits(): void
    {
        $converter = new CurrencyAmountConverter();

        self::assertSame(123, $converter->toMinorUnits('123', 'JPY'));
        self::assertSame('123', $converter->fromMinorUnits(123, 'JPY'));
        self::assertSame(1234, $converter->toMinorUnits('1.234', 'BHD'));
        self::assertSame('1.234', $converter->fromMinorUnits(1234, 'BHD'));
    }

    public function testRejectsExcessCurrencyPrecision(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new CurrencyAmountConverter())->toMinorUnits('100.01', 'JPY');
    }
}
