<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Money;

use Symfony\Component\Intl\Currencies;

final class CurrencyAmountConverter
{
    public function toMinorUnits(string $amount, string $currencyCode): int
    {
        $currencyCode = strtoupper(trim($currencyCode));
        if (!Currencies::exists($currencyCode)) {
            throw new \InvalidArgumentException('Currency code is not a recognized ISO 4217 currency.');
        }

        $fractionDigits = Currencies::getFractionDigits($currencyCode);
        $amount = trim($amount);
        if (1 !== preg_match('/\A(0|[1-9]\d*)(?:\.(\d{1,4}))?\z/', $amount, $matches)) {
            throw new \InvalidArgumentException('Price must be a non-negative decimal amount.');
        }

        $fraction = $matches[2] ?? '';
        if (strlen($fraction) > $fractionDigits) {
            throw new \InvalidArgumentException(sprintf(
                'Currency %s permits at most %d decimal places.',
                $currencyCode,
                $fractionDigits,
            ));
        }

        $minorUnitsText = ltrim($matches[1].str_pad($fraction, $fractionDigits, '0'), '0');
        $minorUnitsText = '' === $minorUnitsText ? '0' : $minorUnitsText;
        $maxMinorUnits = (string) PHP_INT_MAX;
        if (strlen($minorUnitsText) > strlen($maxMinorUnits)
            || (strlen($minorUnitsText) === strlen($maxMinorUnits) && strcmp($minorUnitsText, $maxMinorUnits) > 0)) {
            throw new \InvalidArgumentException('Price is too large.');
        }

        return (int) $minorUnitsText;
    }

    public function fromMinorUnits(int $minorUnits, string $currencyCode): string
    {
        if ($minorUnits < 0 || !Currencies::exists($currencyCode)) {
            throw new \InvalidArgumentException('A non-negative amount and valid currency code are required.');
        }

        $fractionDigits = Currencies::getFractionDigits($currencyCode);
        if (0 === $fractionDigits) {
            return (string) $minorUnits;
        }

        $factor = 10 ** $fractionDigits;

        return intdiv($minorUnits, $factor).'.'.str_pad((string) ($minorUnits % $factor), $fractionDigits, '0', STR_PAD_LEFT);
    }
}
