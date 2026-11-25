<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Money;

use Symfony\Component\Intl\Currencies;

final readonly class PriceFormatter
{
    public function format(int $minorUnits, string $currencyCode): string
    {
        if ($minorUnits < 0 || !Currencies::exists($currencyCode)) {
            throw new \InvalidArgumentException('A non-negative amount and valid currency code are required.');
        }

        $fractionDigits = Currencies::getFractionDigits($currencyCode);
        if (0 === $fractionDigits) {
            return sprintf('%d %s', $minorUnits, $currencyCode);
        }

        $factor = 10 ** $fractionDigits;

        return sprintf(
            '%d.%s %s',
            intdiv($minorUnits, $factor),
            str_pad((string) ($minorUnits % $factor), $fractionDigits, '0', STR_PAD_LEFT),
            $currencyCode,
        );
    }
}
