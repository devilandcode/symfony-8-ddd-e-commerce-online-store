<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Twig;

use App\Storefront\Infrastructure\Money\PriceFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class MoneyExtension extends AbstractExtension
{
    public function __construct(private readonly PriceFormatter $priceFormatter)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('money', $this->priceFormatter->format(...)),
        ];
    }
}
