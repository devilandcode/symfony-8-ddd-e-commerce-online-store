<?php

declare(strict_types=1);

namespace App\Backoffice\Infrastructure\Twig;

use App\Backoffice\Infrastructure\Money\CurrencyAmountConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class PriceExtension extends AbstractExtension
{
    public function __construct(private readonly CurrencyAmountConverter $amountConverter)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('minor_units_amount', $this->formatMinorUnits(...)),
        ];
    }

    public function formatMinorUnits(int $minorUnits, string $currencyCode): string
    {
        return $this->amountConverter->fromMinorUnits($minorUnits, $currencyCode);
    }
}
