<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Price
{
    #[ORM\Column(name: 'price_minor', type: 'bigint', nullable: false)]
    private int $minorUnits;

    #[ORM\Column(name: 'currency_code', type: 'string', length: 3, nullable: false)]
    private string $currencyCode;

    public function __construct(int $minorUnits, string $currencyCode)
    {
        $currencyCode = strtoupper(trim($currencyCode));

        if ($minorUnits < 0) {
            throw new \InvalidArgumentException('Price cannot be negative.');
        }

        if (1 !== preg_match('/\A[A-Z]{3}\z/', $currencyCode)) {
            throw new \InvalidArgumentException('Currency code must contain three ASCII letters.');
        }

        $this->minorUnits = $minorUnits;
        $this->currencyCode = $currencyCode;
    }

    public function getMinorUnits(): int
    {
        return $this->minorUnits;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
